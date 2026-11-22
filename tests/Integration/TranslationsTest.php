<?php
/**
 * Integration tests: translation domains.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration;

/**
 * Plugin and theme must not share a text domain: the first one loaded would hide the other's
 * translations (this left "Friend 432559" untranslated on 2026-11-22).
 */
final class TranslationsTest extends TestCase {

	public function test_plugin_and_theme_use_different_text_domains(): void {
		$root   = dirname( __DIR__, 2 );
		$plugin = get_file_data( $root . '/plugin/euteajudo.php', array( 'domain' => 'Text Domain' ) );
		$theme  = get_file_data( $root . '/theme/style.css', array( 'domain' => 'Text Domain' ) );

		$this->assertSame( 'euteajudo', $plugin['domain'] );
		$this->assertSame( 'euteajudo-theme', $theme['domain'] );
	}
}
