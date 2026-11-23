<?php
/**
 * Integration tests: replies.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\HelpRequests;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\Tests\Integration\TestCase;
use WP_REST_Request;

/**
 * Who can reply, what is stored and how the status moves.
 */
final class RepliesTest extends TestCase {

	/**
	 * Author of the request under test.
	 *
	 * @var \WP_User
	 */
	private \WP_User $author;

	/**
	 * Request under test.
	 *
	 * @var int
	 */
	private int $request = 0;

	public function set_up(): void {
		parent::set_up();
		reset_phpmailer_instance();
		$this->author = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $this->author->ID );
		$id = HelpRequests::create( $this->author->ID, 'Título', 'Texto' );
		$this->assertIsInt( $id );
		$this->request = $id;
	}

	/**
	 * Loads a reply.
	 *
	 * @param int|\WP_Error $id Reply ID.
	 */
	private function reply( int|\WP_Error $id ): \WP_Comment {
		$this->assertIsInt( $id );
		$comment = get_comment( $id );
		$this->assertInstanceOf( \WP_Comment::class, $comment );
		return $comment;
	}

	public function test_author_reply_is_anonymous_masked_and_goes_back_to_triage(): void {
		$_SERVER['REMOTE_ADDR']     = '203.0.113.9';
		$_SERVER['HTTP_USER_AGENT'] = 'Test Browser';

		$reply = $this->reply( Replies::add( $this->request, $this->author->ID, 'Me liga 11912345678' ) );

		$this->assertSame( Type::REPLY, $reply->comment_type );
		$this->assertSame( Aliases::label( Aliases::number_of( $this->author->ID ) ), $reply->comment_author );
		$this->assertSame( '', $reply->comment_author_email );
		$this->assertSame( '', $reply->comment_author_IP );
		$this->assertSame( '', $reply->comment_agent );
		$this->assertStringNotContainsString( '11912345678', $reply->comment_content );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $this->request ) );
	}

	public function test_reply_from_someone_who_helps_moves_to_in_conversation_without_core_emails(): void {
		$admin = $this->new_user( array( 'role' => 'administrator' ) );

		$this->reply( Replies::add( $this->request, $admin->ID, 'Estou aqui com você.' ) );

		$this->assertSame( Type::IN_CONVERSATION, get_post_status( $this->request ) );
		$this->assertTrue( $this->no_email_sent(), 'Core comment notifications would leak the reply text.' );
	}

	public function test_other_friends_cannot_reply_and_closed_requests_take_no_replies(): void {
		$stranger = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $stranger->ID );

		$this->assertSame( 'not_allowed', $this->error_code( Replies::add( $this->request, $stranger->ID, 'Oi' ) ) );

		$this->assertSame( 'empty', $this->error_code( Replies::add( $this->request, $this->author->ID, '  ' ) ) );

		HelpRequests::close( $this->request, $this->author->ID, 'unspecified' );
		$this->assertSame( 'closed', $this->error_code( Replies::add( $this->request, $this->author->ID, 'Oi' ) ) );
	}

	public function test_author_thanks_replies_from_others_only(): void {
		$admin  = $this->new_user( array( 'role' => 'administrator' ) );
		$theirs = $this->reply( Replies::add( $this->request, $admin->ID, 'Oi' ) );
		$mine   = $this->reply( Replies::add( $this->request, $this->author->ID, 'Obrigado' ) );

		$this->assertTrue( Replies::toggle_thanks( (int) $theirs->comment_ID, $this->author->ID ) );
		$this->assertTrue( Replies::is_thanked( (int) $theirs->comment_ID ) );
		$this->assertFalse( Replies::toggle_thanks( (int) $mine->comment_ID, $this->author->ID ) );
		$this->assertFalse( Replies::toggle_thanks( (int) $theirs->comment_ID, $admin->ID ) );

		Replies::toggle_thanks( (int) $theirs->comment_ID, $this->author->ID );
		$this->assertFalse( Replies::is_thanked( (int) $theirs->comment_ID ) );
	}

	public function test_replies_do_not_leak_through_the_rest_api_or_comment_feeds(): void {
		$this->reply( Replies::add( $this->request, $this->author->ID, 'Segredo' ) );
		wp_set_current_user( 0 );

		$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/comments' ) );
		$this->assertStringNotContainsString( 'Segredo', (string) wp_json_encode( $response->get_data() ) );

		$feed = new \WP_Comment_Query( array( 'type' => Type::REPLY ) );
		$this->go_to( get_feed_link( 'comments_rss2' ) );
		$this->assertSame( array(), get_comments( array( 'post_type' => 'post' ) ) );
		$this->assertNotEmpty( $feed->comments, 'Sanity check: the reply exists.' );
		$this->assertNotContains( Type::REPLY, wp_list_pluck( $GLOBALS['wp_query']->comments ?? array(), 'comment_type' ) );
	}

	/**
	 * Error code of a result.
	 *
	 * @param int|\WP_Error $result Result.
	 */
	private function error_code( int|\WP_Error $result ): string {
		$this->assertInstanceOf( \WP_Error::class, $result );
		return (string) $result->get_error_code();
	}
}
