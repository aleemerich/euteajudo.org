<?php
/**
 * Integration tests: account pages.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts;

use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Sign-in, sign-up and recovery pages exist once and hold their block.
 */
final class PagesTest extends TestCase {

	public function test_install_creates_each_page_once_with_its_block(): void {
		Pages::install();
		Pages::install();

		foreach ( Pages::BLOCKS as $key => $block ) {
			$page = get_post( Pages::id( $key ) );
			$this->assertInstanceOf( \WP_Post::class, $page, $key );
			$this->assertSame( 'publish', $page->post_status, $key );
			$this->assertStringContainsString( '<!-- wp:' . $block . ' /-->', $page->post_content, $key );
			$this->assertCount(
				1,
				get_posts(
					array(
						'post_type' => 'page',
						's'         => $block,
						'fields'    => 'ids',
					)
				),
				$key
			);
		}
	}

	public function test_url_points_to_the_page(): void {
		Pages::install();

		$this->assertSame( get_permalink( Pages::id( Pages::SIGN_IN ) ), Pages::url( Pages::SIGN_IN ) );
	}

	public function test_account_pages_are_not_indexed(): void {
		Pages::install();
		$this->go_to( Pages::url( Pages::SIGN_IN ) );

		$robots = apply_filters( 'wp_robots', array() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook under test.

		$this->assertTrue( $robots['noindex'] ?? false );
	}
}
