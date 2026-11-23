<?php
/**
 * Integration tests: the Agent API.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Agents;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Agents\AgentAliases;
use EuTeAjudo\Agents\AgentWork;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\TriageTerms;
use EuTeAjudo\Migrations;
use EuTeAjudo\SupportServices\SupportServices;
use EuTeAjudo\Tests\Integration\TestCase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * A simulated Agent lists pending work, reserves, triages, replies, gets a conflict and sees
 * the status change. Nobody else reaches the API.
 */
final class AgentApiTest extends TestCase {

	/**
	 * Author of the request.
	 *
	 * @var \WP_User
	 */
	private \WP_User $author;

	/**
	 * Agent under test.
	 *
	 * @var \WP_User
	 */
	private \WP_User $agent;

	/**
	 * Request under test.
	 *
	 * @var int
	 */
	private int $request = 0;

	public function set_up(): void {
		parent::set_up();
		reset_phpmailer_instance();
		TriageTerms::install();
		Roles::install();
		Migrations::agent_capabilities();
		AgentAliases::install();
		SupportServices::install();
		$this->author = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $this->author->ID );
		$this->agent = $this->new_user( array( 'role' => Roles::AGENT ) );
		$id          = HelpRequests::create( $this->author->ID, 'Não consigo dormir', 'Penso em sumir.' );
		$this->assertIsInt( $id );
		$this->request = $id;
		wp_set_current_user( $this->agent->ID );
	}

	/**
	 * Calls the API.
	 *
	 * @param string               $method HTTP method.
	 * @param string               $path   Path under /euteajudo/v1.
	 * @param array<string, mixed> $body   JSON body.
	 */
	private function call( string $method, string $path, array $body = array() ): WP_REST_Response {
		$request = new WP_REST_Request( $method, '/euteajudo/v1' . $path );
		if ( array() !== $body ) {
			$request->set_header( 'Content-Type', 'application/json' );
			$request->set_body( (string) wp_json_encode( $body ) );
		}
		$response = rest_do_request( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		return $response;
	}

	/**
	 * Response data as an array.
	 *
	 * @param WP_REST_Response $response Response.
	 * @return array<string, mixed>
	 */
	private function data( WP_REST_Response $response ): array {
		$data = $response->get_data();
		$this->assertIsArray( $data );
		return $data;
	}

	/**
	 * Triage body.
	 *
	 * @return array<string, mixed>
	 */
	private function triage(): array {
		return array(
			'categories'   => array( 'emotional' ),
			'severity'     => 'high',
			'risk_signals' => array( 'suicidal-ideation' ),
			'summary'      => 'Insônia e pensamento de sumir depois de perder o emprego.',
		);
	}

	public function test_full_agent_cycle(): void {
		$queue = $this->data( $this->call( 'GET', '/queue' ) );
		$this->assertSame( array( $this->request ), array_column( $queue['items'], 'id' ) );

		$this->assertSame( 200, $this->call( 'POST', "/requests/{$this->request}/reservation" )->get_status() );

		$detail = $this->data( $this->call( 'GET', "/requests/{$this->request}" ) );
		$this->assertSame( 'Penso em sumir.', $detail['messages'][0]['text'] );
		$this->assertSame( 0, $detail['last_author_message_id'] );
		$this->assertMatchesRegularExpression( '/^Friend \d+$|^Amigo \d+$/', $detail['agent_alias'] );
		$this->assertArrayNotHasKey( 'author', $detail );

		$this->assertSame( 200, $this->call( 'POST', "/requests/{$this->request}/triage", $this->triage() )->get_status() );
		$this->assertSame( Type::AWAITING_REPLY, get_post_status( $this->request ) );
		$this->assertTrue( has_term( 'high', Type::SEVERITY, $this->request ) );
		$this->assertSame( 'Insônia e pensamento de sumir depois de perder o emprego.', get_post_field( 'post_excerpt', $this->request ) );

		$detail = $this->data( $this->call( 'GET', "/requests/{$this->request}" ) );
		$this->assertContains( 'CVV', array_map( static fn ( array $s ): string => substr( $s['name'], 0, 3 ), $detail['support_services'] ) );

		$response = $this->call(
			'POST',
			"/requests/{$this->request}/replies",
			array(
				'text'        => 'Estou aqui com você. Se o pensamento apertar, o CVV atende no 188.',
				'in_reply_to' => 0,
			)
		);
		$this->assertSame( 201, $response->get_status() );
		$reply = get_comment( (int) $this->data( $response )['id'] );
		$this->assertInstanceOf( \WP_Comment::class, $reply );
		$this->assertSame( $detail['agent_alias'], $reply->comment_author );
		$this->assertNotSame( Aliases::label( Aliases::number_of( $this->agent->ID ) ), $reply->comment_author );
		$this->assertSame( Type::IN_CONVERSATION, get_post_status( $this->request ) );
		$this->assertSame( array(), $this->data( $this->call( 'GET', '/queue' ) )['items'] );
		$this->assertGreaterThan( 0, AgentWork::last_seen( $this->agent->ID ) );
	}

	public function test_reply_is_refused_when_the_author_wrote_again(): void {
		$this->call( 'POST', "/requests/{$this->request}/reservation" );
		$this->call( 'POST', "/requests/{$this->request}/triage", $this->triage() );
		$author_reply = Replies::add( $this->request, $this->author->ID, 'Mais uma coisa.' );
		$this->assertIsInt( $author_reply );

		$response = $this->call(
			'POST',
			"/requests/{$this->request}/replies",
			array(
				'text'        => 'Oi.',
				'in_reply_to' => 0,
			)
		);
		$this->assertSame( 409, $response->get_status() );
		$this->assertSame( 'eta_author_wrote_again', $this->data( $response )['code'] );
		$this->assertSame( 0, (int) get_comments_number( $this->request ) - 1 );

		$this->call( 'POST', "/requests/{$this->request}/triage", $this->triage() );
		$detail = $this->data( $this->call( 'GET', "/requests/{$this->request}" ) );
		$this->assertSame( $author_reply, $detail['last_author_message_id'] );
		$this->assertSame(
			201,
			$this->call(
				'POST',
				"/requests/{$this->request}/replies",
				array(
					'text'        => 'Oi.',
					'in_reply_to' => $author_reply,
				)
			)->get_status()
		);
	}

	public function test_reservation_keeps_other_agents_out(): void {
		$this->call( 'POST', "/requests/{$this->request}/reservation" );
		$other = $this->new_user( array( 'role' => Roles::AGENT ) );
		wp_set_current_user( $other->ID );

		$this->assertSame( 409, $this->call( 'POST', "/requests/{$this->request}/reservation" )->get_status() );
		$this->assertSame( array(), $this->data( $this->call( 'GET', '/queue' ) )['items'] );
		$this->assertSame( 409, $this->call( 'POST', "/requests/{$this->request}/triage", $this->triage() )->get_status() );

		wp_set_current_user( $this->agent->ID );
		$this->assertSame( 200, $this->call( 'DELETE', "/requests/{$this->request}/reservation" )->get_status() );
		wp_set_current_user( $other->ID );
		$this->assertSame( 200, $this->call( 'POST', "/requests/{$this->request}/reservation" )->get_status() );
	}

	public function test_failures_back_off_and_alert_the_administrator_once(): void {
		$this->call( 'POST', "/requests/{$this->request}/reservation" );
		$first = $this->data( $this->call( 'POST', "/requests/{$this->request}/failures" ) );
		$this->assertSame( 1, $first['failures'] );
		$this->assertSame( array(), $this->data( $this->call( 'GET', '/queue' ) )['items'] );
		$this->assertStringContainsString( 'EuTeAjudo', $this->last_email()['subject'] );
		$this->assertStringNotContainsString( 'sumir', $this->last_email()['body'] );

		reset_phpmailer_instance();
		update_post_meta( $this->request, AgentWork::NEXT_ATTEMPT_META, time() - 1 );
		$this->call( 'POST', "/requests/{$this->request}/reservation" );
		$second = $this->data( $this->call( 'POST', "/requests/{$this->request}/failures" ) );
		$this->assertSame( 2, $second['failures'] );
		$this->assertGreaterThan( $first['retry_in'], $second['retry_in'] );
		$this->assertTrue( $this->no_email_sent() );

		Replies::add( $this->request, $this->author->ID, 'Ainda estou aqui.' );
		$this->assertSame( 0, AgentWork::failures( $this->request ) );
		$this->assertSame( array( $this->request ), array_column( $this->data( $this->call( 'GET', '/queue' ) )['items'], 'id' ) );
	}

	public function test_only_agents_reach_the_api(): void {
		foreach ( array( 0, $this->author->ID, $this->new_user( array( 'role' => 'administrator' ) )->ID ) as $user ) {
			wp_set_current_user( $user );
			$this->assertContains( $this->call( 'GET', '/queue' )->get_status(), array( 401, 403 ) );
			$this->assertContains( $this->call( 'GET', "/requests/{$this->request}" )->get_status(), array( 401, 403 ) );
		}
	}

	public function test_closed_requests_are_out_of_reach(): void {
		HelpRequests::close( $this->request, $this->author->ID, 'better' );

		$this->assertSame( array(), $this->data( $this->call( 'GET', '/queue' ) )['items'] );
		$this->assertSame( 404, $this->call( 'GET', "/requests/{$this->request}" )->get_status() );
	}

	public function test_triage_rejects_unknown_terms(): void {
		$this->call( 'POST', "/requests/{$this->request}/reservation" );
		$body             = $this->triage();
		$body['severity'] = 'apocalyptic';

		$this->assertSame( 400, $this->call( 'POST', "/requests/{$this->request}/triage", $body )->get_status() );
	}

	public function test_agent_aliases_are_never_given_to_people(): void {
		$bank = AgentAliases::all();
		$this->assertCount( AgentAliases::SIZE, $bank );
		$user = $this->new_user( array( 'role' => Roles::FRIEND ) );
		$this->assertNotContains( Aliases::number_of( $user->ID ), $bank );
		$this->assertSame( AgentAliases::for_request( $this->request ), AgentAliases::for_request( $this->request ) );
	}
}
