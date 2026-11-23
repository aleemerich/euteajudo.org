<?php
/**
 * Integration tests: help request forms and the conversation address.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\HelpRequests;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\HelpRequests\Conversations;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\RequestForms;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Writing, replying, closing, reopening and thanking through the forms.
 */
final class RequestFormsTest extends TestCase {

	/**
	 * Signed-in, confirmed Friend.
	 *
	 * @var \WP_User
	 */
	private \WP_User $author;

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		Conversations::add_rule();
		$this->set_permalink_structure( '/%postname%/' );
		$this->author = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $this->author->ID );
		wp_set_current_user( $this->author->ID );
	}

	/**
	 * Query parameter of a URL.
	 *
	 * @param string $url URL.
	 * @param string $key Parameter.
	 */
	private function param( string $url, string $key ): string {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		return is_string( $query[ $key ] ?? null ) ? $query[ $key ] : '';
	}

	/**
	 * Writes a request through the form and returns it.
	 */
	private function write(): \WP_Post {
		$url = RequestForms::write(
			array(
				'title' => 'Não consigo dormir',
				'body'  => 'Faz três semanas que fui demitido.',
			)
		);
		$this->assertSame( 'sent', $this->param( $url, 'eta_step' ) );
		$code = basename( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		$post = HelpRequests::find_by_code( $code );
		$this->assertInstanceOf( \WP_Post::class, $post );
		return $post;
	}

	public function test_writing_leads_to_the_conversation_under_my_requests(): void {
		$post = $this->write();

		$this->assertSame( trailingslashit( Pages::url( Pages::MY_REQUESTS ) ) . $post->post_name . '/', Conversations::url( $post ) );
	}

	public function test_writing_errors_come_back_with_the_text_kept(): void {
		$url = RequestForms::write(
			array(
				'title' => '',
				'body'  => 'Um texto longo que não pode se perder.',
			)
		);

		$state = $this->form_state( $url );
		$this->assertSame( array( 'title_required' ), $state['errors'] );
		$this->assertSame( 'Um texto longo que não pode se perder.', $state['values']['body'] );
		$this->assertStringStartsWith( Pages::url( Pages::WRITE ), $url );
	}

	public function test_reply_close_reopen_and_thanks_through_the_forms(): void {
		$post   = $this->write();
		$admin  = $this->new_user( array( 'role' => 'administrator' ) );
		$theirs = Replies::add( $post->ID, $admin->ID, 'Estou aqui.' );
		$this->assertIsInt( $theirs );

		$url = RequestForms::reply(
			array(
				'code' => $post->post_name,
				'text' => 'Obrigado.',
			)
		);
		$this->assertSame( 'reply_sent', $this->param( $url, 'eta_step' ) );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $post->ID ) );

		RequestForms::thanks(
			array(
				'code'  => $post->post_name,
				'reply' => (string) $theirs,
			)
		);
		$this->assertTrue( Replies::is_thanked( $theirs ) );

		RequestForms::close(
			array(
				'code'   => $post->post_name,
				'reason' => 'better',
			)
		);
		$this->assertSame( Type::CLOSED, get_post_status( $post->ID ) );

		RequestForms::reopen( array( 'code' => $post->post_name ) );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $post->ID ) );
	}

	public function test_someone_else_gets_not_found_for_the_conversation(): void {
		$post     = $this->write();
		$stranger = $this->new_user( array( 'role' => Roles::FRIEND ) );
		wp_set_current_user( $stranger->ID );

		$this->assertNull( Conversations::visible_request( $post->post_name ) );
		$this->assertSame(
			'not_found',
			$this->param(
				RequestForms::reply(
					array(
						'code' => $post->post_name,
						'text' => 'Oi',
					)
				),
				'eta_auth_error'
			)
		);
		$this->assertSame( 0, (int) get_comments_number( $post->ID ) );
	}

	public function test_conversation_address_resolves_to_the_my_requests_page(): void {
		$post = $this->write();

		$this->go_to( Conversations::url( $post ) );

		$this->assertTrue( is_page( Pages::id( Pages::MY_REQUESTS ) ) );
		$this->assertSame( $post->post_name, get_query_var( Conversations::QUERY_VAR ) );
	}
}
