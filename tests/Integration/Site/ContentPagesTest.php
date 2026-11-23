<?php
/**
 * Integration tests: public content pages.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Site;

use EuTeAjudo\Site\ContentPages;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * About, Terms of use and Privacy policy.
 */
final class ContentPagesTest extends TestCase {

	public function test_install_creates_the_pages_once_and_sets_the_privacy_page(): void {
		ContentPages::install();
		ContentPages::install();

		foreach ( ContentPages::KEYS as $key ) {
			$this->assertNotSame( '', ContentPages::url( $key ), $key );
		}
		$this->assertSame( ContentPages::url( ContentPages::PRIVACY ), get_privacy_policy_url() );
		$this->assertCount(
			1,
			get_posts(
				array(
					'post_type' => 'page',
					'name'      => 'termos-de-uso',
				)
			)
		);
	}

	public function test_pages_with_fields_to_fill_are_reported_until_filled(): void {
		ContentPages::install();
		$this->assertSame( array(), ContentPages::with_placeholders(), 'The shipped pages are complete.' );

		$id = (int) get_option( ContentPages::OPTION )[ ContentPages::PRIVACY ];
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => '<!-- wp:paragraph --><p>Contato: [preencher: e-mail].</p><!-- /wp:paragraph -->',
			)
		);
		$this->assertArrayHasKey( ContentPages::PRIVACY, ContentPages::with_placeholders() );

		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => '<!-- wp:paragraph --><p>Pronto.</p><!-- /wp:paragraph -->',
			)
		);

		$this->assertArrayNotHasKey( ContentPages::PRIVACY, ContentPages::with_placeholders() );
	}

	public function test_texts_describe_what_the_code_does(): void {
		ContentPages::install();
		$ids     = get_option( ContentPages::OPTION );
		$privacy = get_post( (int) $ids[ ContentPages::PRIVACY ] )->post_content ?? '';

		$this->assertStringContainsString( '[removido para proteger você]', $privacy, 'Matches the mask placeholder.' );
		$this->assertStringContainsString( '30 dias', get_post( (int) $ids[ ContentPages::TERMS ] )->post_content ?? '' );
	}
}
