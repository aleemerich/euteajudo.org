<?php
/**
 * Integration tests: operating help requests (E6).
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\HelpRequests;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Agents\AgentAliases;
use EuTeAjudo\Agents\AgentWork;
use EuTeAjudo\HelpRequests\EditAudit;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\Schedules;
use EuTeAjudo\Admin\HelpRequestsAdmin;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Edits with audit, administrator replies with each alias, notifications and schedules.
 */
final class OperationTest extends TestCase {

	/**
	 * Author of the request.
	 *
	 * @var \WP_User
	 */
	private \WP_User $author;

	/**
	 * Administrator.
	 *
	 * @var \WP_User
	 */
	private \WP_User $admin;

	/**
	 * Request under test.
	 *
	 * @var int
	 */
	private int $request = 0;

	public function set_up(): void {
		parent::set_up();
		reset_phpmailer_instance();
		Pages::install();
		AgentAliases::install();
		$this->author = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $this->author->ID );
		$this->admin = $this->new_user( array( 'role' => 'administrator' ) );
		$id          = HelpRequests::create( $this->author->ID, 'Título original', 'Texto original' );
		$this->assertIsInt( $id );
		$this->request = $id;
	}

	/**
	 * Moves the request's activity into the past.
	 *
	 * @param int $seconds How long ago.
	 */
	private function age( int $seconds ): void {
		global $wpdb;
		$date = gmdate( 'Y-m-d H:i:s', time() - $seconds );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Test setup: there is no API to backdate.
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_date_gmt' => $date,
				'post_date'     => $date,
			),
			array( 'ID' => $this->request )
		);
		$wpdb->update(
			$wpdb->comments,
			array(
				'comment_date_gmt' => $date,
				'comment_date'     => $date,
			),
			array( 'comment_post_ID' => $this->request )
		);
		// phpcs:enable
		clean_post_cache( $this->request );
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d", $this->request ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_comment_cache( array_map( 'intval', $ids ) );
		update_post_meta( $this->request, HelpRequests::AUTHOR_ACTIVE_META, time() - $seconds );
	}

	public function test_editing_a_request_keeps_the_original_and_who_edited(): void {
		wp_set_current_user( $this->admin->ID );
		wp_update_post(
			array(
				'ID'           => $this->request,
				'post_content' => 'Texto corrigido',
			)
		);
		wp_update_post(
			array(
				'ID'           => $this->request,
				'post_content' => 'Texto corrigido de novo',
			)
		);

		$this->assertSame( 'Texto original', get_post_meta( $this->request, EditAudit::ORIGINAL_CONTENT_META, true ) );
		$log = EditAudit::log( $this->request );
		$this->assertCount( 2, $log );
		$this->assertSame( $this->admin->ID, $log[0]['user'] );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $this->request ), 'Editing does not move the status.' );
	}

	public function test_editing_a_reply_keeps_the_original(): void {
		$reply = Replies::add( $this->request, $this->author->ID, 'Resposta original' );
		$this->assertIsInt( $reply );
		wp_set_current_user( $this->admin->ID );

		wp_update_comment(
			array(
				'comment_ID'      => $reply,
				'comment_content' => 'Resposta editada',
			)
		);

		$this->assertSame( 'Resposta original', get_comment_meta( $reply, EditAudit::ORIGINAL_CONTENT_META, true ) );
		$this->assertSame( $this->admin->ID, (int) get_comment_meta( $reply, EditAudit::EDITED_BY_META, true ) );
	}

	public function test_the_admin_editor_cannot_change_the_status_by_saving(): void {
		wp_set_current_user( $this->admin->ID );
		wp_update_post(
			array(
				'ID'          => $this->request,
				'post_status' => 'publish',
			)
		);

		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $this->request ) );
	}

	public function test_admin_replies_with_own_alias_or_the_agent_alias_of_the_request(): void {
		$own = HelpRequestsAdmin::reply( $this->request, $this->admin->ID, 'Com meu Apelido.', false );
		$as  = HelpRequestsAdmin::reply( $this->request, $this->admin->ID, 'Com o Apelido do Agente.', true );
		$this->assertIsInt( $own );
		$this->assertIsInt( $as );

		$own_reply = get_comment( $own );
		$as_reply  = get_comment( $as );
		$this->assertInstanceOf( \WP_Comment::class, $own_reply );
		$this->assertInstanceOf( \WP_Comment::class, $as_reply );
		$this->assertSame( Aliases::label( Aliases::number_of( $this->admin->ID ) ), $own_reply->comment_author );
		$this->assertSame( AgentAliases::label_for_request( $this->request ), $as_reply->comment_author );
		$this->assertSame( $this->admin->ID, (int) $as_reply->user_id, 'Real authorship stays with the administrator.' );
		$this->assertSame( 'agent', get_comment_meta( $as, HelpRequestsAdmin::ALIAS_USED_META, true ) );
		$this->assertSame( Type::IN_CONVERSATION, get_post_status( $this->request ) );
	}

	public function test_author_gets_a_neutral_email_for_a_new_reply_but_not_for_their_own(): void {
		Replies::add( $this->request, $this->author->ID, 'Eu de novo' );
		$this->assertTrue( $this->no_email_sent() );

		Replies::add( $this->request, $this->admin->ID, 'Uma Resposta com detalhe sensível' );
		$email = $this->last_email();
		$this->assertSame( $this->author->user_email, $email['to'] );
		$this->assertStringNotContainsString( 'sensível', $email['subject'] . $email['body'] );
		$this->assertStringNotContainsString( 'Título original', $email['subject'] . $email['body'] );
		$this->assertStringContainsString( get_post( $this->request )->post_name ?? 'missing', $email['body'] );
	}

	public function test_requests_inactive_for_30_days_are_closed_and_the_author_told(): void {
		Replies::add( $this->request, $this->admin->ID, 'Oi' );
		$this->age( 31 * DAY_IN_SECONDS );
		$recent = HelpRequests::create( $this->author->ID, 'Recente', 'Texto' );
		$this->assertIsInt( $recent );
		reset_phpmailer_instance();

		$this->assertSame( 1, Schedules::close_inactive() );

		$this->assertSame( Type::CLOSED, get_post_status( $this->request ) );
		$this->assertSame( 'system', get_post_meta( $this->request, HelpRequests::CLOSED_BY_META, true ) );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $recent ) );
		$this->assertSame( $this->author->user_email, $this->last_email()['to'] );
		$this->assertStringNotContainsString( 'Título original', $this->last_email()['body'] );
	}

	public function test_administrator_is_alerted_once_about_requests_without_agent_reply_for_24_hours(): void {
		$this->age( 25 * HOUR_IN_SECONDS );

		$this->assertSame( 1, Schedules::alert_late() );
		$this->assertSame( get_option( 'admin_email' ), $this->last_email()['to'] );
		$this->assertStringNotContainsString( 'Texto original', $this->last_email()['body'] );

		reset_phpmailer_instance();
		$this->assertSame( 0, Schedules::alert_late() );
		$this->assertTrue( $this->no_email_sent() );
	}

	public function test_list_filters_late_and_failed_requests(): void {
		$late = HelpRequests::create( $this->author->ID, 'Atrasado', 'Texto' );
		$this->assertIsInt( $late );
		update_post_meta( $late, HelpRequests::AUTHOR_ACTIVE_META, time() - 2 * DAY_IN_SECONDS );
		update_post_meta( $this->request, AgentWork::FAILURES_META, 2 );

		$ids = static fn ( string $situation ): array => array_map(
			static fn ( \WP_Post $post ): int => $post->ID,
			get_posts(
				array(
					'post_type'   => Type::POST_TYPE,
					'post_status' => Type::STATUSES,
				) + HelpRequestsAdmin::situation_query( $situation )
			)
		);

		$this->assertSame( array( $late ), $ids( 'late' ) );
		$this->assertSame( array( $this->request ), $ids( 'failed' ) );
	}
}
