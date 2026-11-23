<?php
/**
 * Integration tests: only one request runs migrations at a time.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration;

use EuTeAjudo\Plugin;

/**
 * Concurrent first visits after a deploy must not run the same migrations twice.
 */
final class MigrationLockTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		update_option( Plugin::SCHEMA_OPTION, '0.0.0' );
	}

	public function tear_down(): void {
		delete_option( Plugin::LOCK_OPTION );
		Plugin::migrate();
		parent::tear_down();
	}

	public function test_skips_while_another_request_holds_the_lock(): void {
		add_option( Plugin::LOCK_OPTION, time(), '', false );

		Plugin::migrate();

		$this->assertSame( '0.0.0', get_option( Plugin::SCHEMA_OPTION ) );
	}

	public function test_takes_over_a_stale_lock(): void {
		add_option( Plugin::LOCK_OPTION, time() - Plugin::LOCK_TTL - 1, '', false );

		Plugin::migrate();

		$this->assertSame( \EuTeAjudo\VERSION, get_option( Plugin::SCHEMA_OPTION ) );
		$this->assertFalse( get_option( Plugin::LOCK_OPTION ) );
	}

	public function test_releases_the_lock_after_migrating(): void {
		Plugin::migrate();

		$this->assertSame( \EuTeAjudo\VERSION, get_option( Plugin::SCHEMA_OPTION ) );
		$this->assertFalse( get_option( Plugin::LOCK_OPTION ) );
	}
}
