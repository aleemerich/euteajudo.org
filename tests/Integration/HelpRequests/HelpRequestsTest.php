<?php
/**
 * Integration tests: help requests.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\HelpRequests;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\TriageTerms;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Creation rules, access, closure and privacy of help requests.
 */
final class HelpRequestsTest extends TestCase {

	/**
	 * A confirmed Friend.
	 */
	private function friend(): \WP_User {
		$user = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $user->ID );
		return $user;
	}

	/**
	 * Creates a request and returns its ID.
	 *
	 * @param \WP_User $author Author.
	 */
	private function request_by( \WP_User $author ): int {
		$id = HelpRequests::create( $author->ID, 'Não consigo dormir', 'Faz três semanas que fui demitido.' );
		$this->assertIsInt( $id );
		return $id;
	}

	public function test_post_type_statuses_and_taxonomies_are_registered_privately(): void {
		$type = get_post_type_object( Type::POST_TYPE );
		$this->assertNotNull( $type );
		$this->assertFalse( $type->public );
		$this->assertFalse( $type->publicly_queryable );
		$this->assertTrue( $type->exclude_from_search );
		foreach ( Type::STATUSES as $status ) {
			$object = get_post_status_object( $status );
			$this->assertNotNull( $object, $status );
			$this->assertFalse( $object->public, $status );
			$this->assertLessThanOrEqual( 20, strlen( $status ) );
		}
		foreach ( Type::TAXONOMIES as $taxonomy ) {
			$object = get_taxonomy( $taxonomy );
			$this->assertInstanceOf( \WP_Taxonomy::class, $object, $taxonomy );
			$this->assertFalse( $object->public, $taxonomy );
		}
	}

	public function test_initial_triage_terms_exist(): void {
		// The WordPress test suite wipes terms between test classes, so install them here.
		TriageTerms::install();
		TriageTerms::install();

		$expected = array(
			Type::CATEGORY    => 8,
			Type::SEVERITY    => 4,
			Type::RISK_SIGNAL => 10,
		);
		foreach ( $expected as $taxonomy => $count ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);
			$this->assertIsArray( $terms );
			$this->assertCount( $count, $terms, $taxonomy );
		}
	}

	public function test_confirmed_friend_creates_a_request_awaiting_triage_with_a_random_code(): void {
		$author = $this->friend();

		$id = HelpRequests::create( $author->ID, 'Meu zap 11912345678', 'Escreve em maria@example.org, por favor.' );

		$this->assertIsInt( $id );
		$post = get_post( $id );
		$this->assertInstanceOf( \WP_Post::class, $post );
		$this->assertSame( Type::POST_TYPE, $post->post_type );
		$this->assertSame( Type::AWAITING_TRIAGE, $post->post_status );
		$this->assertSame( $author->ID, (int) $post->post_author );
		$this->assertMatchesRegularExpression( '/^[a-z0-9]{12}$/', $post->post_name );
		$this->assertStringNotContainsString( '11912345678', $post->post_title );
		$this->assertStringNotContainsString( 'maria@example.org', $post->post_content );
		$this->assertSame( $id, HelpRequests::find_by_code( $post->post_name )?->ID );
	}

	/**
	 * Requests that must be refused, with the error code.
	 *
	 * @return array<string, array{0: string, 1: string, 2: string}>
	 */
	public static function refused(): array {
		return array(
			'empty title' => array( 'confirmed', '', 'title_required' ),
			'empty body'  => array( 'confirmed', 'body', 'body_required' ),
			'unconfirmed' => array( 'unconfirmed', '', 'email_unconfirmed' ),
			'deleted'     => array( 'deleted', '', 'not_allowed' ),
			'guest'       => array( 'guest', '', 'not_allowed' ),
		);
	}

	/**
	 * Refuses each invalid request.
	 *
	 * @dataProvider refused
	 *
	 * @param string $who   Kind of user.
	 * @param string $blank Which field to leave empty.
	 * @param string $code  Expected error code.
	 */
	public function test_refuses_invalid_requests( string $who, string $blank, string $code ): void {
		$user_id = 0;
		if ( 'guest' !== $who ) {
			$user    = 'unconfirmed' === $who ? $this->new_user( array( 'role' => Roles::FRIEND ) ) : $this->friend();
			$user_id = $user->ID;
			if ( 'deleted' === $who ) {
				AccountDeletion::delete( $user_id );
			}
		}

		$result = HelpRequests::create( $user_id, '' === $blank ? '' : 'Título', 'body' === $blank ? '   ' : 'Texto' );
		if ( '' === $blank && 'confirmed' !== $who ) {
			$result = HelpRequests::create( $user_id, 'Título', 'Texto' );
		}

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( $code, $result->get_error_code() );
	}

	public function test_only_the_author_and_administrators_can_view(): void {
		$author = $this->friend();
		$id     = $this->request_by( $author );

		$this->assertTrue( HelpRequests::can_view( $id, $author->ID ) );
		$this->assertFalse( HelpRequests::can_view( $id, $this->friend()->ID ) );
		$this->assertFalse( HelpRequests::can_view( $id, 0 ) );
		$this->assertTrue( HelpRequests::can_view( $id, $this->new_user( array( 'role' => 'administrator' ) )->ID ) );
	}

	public function test_requests_never_reach_search_feeds_or_public_urls(): void {
		$id = $this->request_by( $this->friend() );

		$search = new \WP_Query( array( 's' => 'dormir' ) );
		$found  = array_map( static fn ( $post ): int => $post instanceof \WP_Post ? $post->ID : (int) $post, (array) $search->posts );
		$this->assertNotContains( $id, $found );

		$this->go_to( add_query_arg( 'p', $id, home_url( '/' ) ) );
		$this->assertTrue( is_404() );

		$this->go_to(
			add_query_arg(
				array(
					'post_type' => Type::POST_TYPE,
					'p'         => $id,
				),
				home_url( '/' )
			)
		);
		$this->assertFalse( have_posts() );
	}

	public function test_author_closes_with_a_reason_and_reopens(): void {
		$author = $this->friend();
		$id     = $this->request_by( $author );

		$this->assertInstanceOf( \WP_Error::class, HelpRequests::close( $id, $this->friend()->ID, 'better' ) );
		$this->assertTrue( HelpRequests::close( $id, $author->ID, 'better' ) );
		$this->assertSame( Type::CLOSED, get_post_status( $id ) );
		$this->assertSame( 'better', get_post_meta( $id, HelpRequests::CLOSED_REASON_META, true ) );

		$this->assertTrue( HelpRequests::reopen( $id, $author->ID ) );
		$this->assertSame( Type::AWAITING_TRIAGE, get_post_status( $id ), 'No reply yet: back to triage.' );

		$admin = $this->new_user( array( 'role' => 'administrator' ) );
		Replies::add( $id, $admin->ID, 'Estou aqui.' );
		HelpRequests::close( $id, $author->ID, 'no_more' );
		HelpRequests::reopen( $id, $author->ID );
		$this->assertSame( Type::IN_CONVERSATION, get_post_status( $id ), 'Last message was a reply: in conversation.' );
	}

	public function test_unseen_reply_indicator_follows_replies_and_visits(): void {
		$author = $this->friend();
		$id     = $this->request_by( $author );
		$this->assertFalse( HelpRequests::has_unseen_reply( $id ) );

		Replies::add( $id, $this->new_user( array( 'role' => 'administrator' ) )->ID, 'Oi.' );
		$this->assertTrue( HelpRequests::has_unseen_reply( $id ) );

		HelpRequests::mark_seen( $id );
		$this->assertFalse( HelpRequests::has_unseen_reply( $id ) );
	}
}
