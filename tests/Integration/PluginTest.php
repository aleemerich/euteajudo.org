<?php
/**
 * Integration tests for the plugin bootstrap.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration;

use EuTeAjudo\Plugin;
use WP_UnitTestCase;

use const EuTeAjudo\VERSION;

/**
 * The plugin loads inside WordPress and records its schema version.
 */
final class PluginTest extends WP_UnitTestCase {

	public function test_boot_records_schema_version(): void {
		$this->assertSame( VERSION, get_option( Plugin::SCHEMA_OPTION ) );
	}

	public function test_textdomain_loader_is_hooked_on_init(): void {
		$this->assertSame( 10, has_action( 'init', array( Plugin::class, 'load_textdomain' ) ) );
	}
}
