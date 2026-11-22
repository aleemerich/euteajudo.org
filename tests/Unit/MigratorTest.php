<?php
/**
 * Unit tests for the version-based migrator.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit;

use EuTeAjudo\Migrator;
use PHPUnit\Framework\TestCase;

/**
 * Migration ordering and version bookkeeping.
 */
final class MigratorTest extends TestCase {

	/**
	 * Versions whose migration callbacks ran, in call order.
	 *
	 * @var list<string>
	 */
	private array $ran = array();

	/**
	 * Schema version as persisted by the migrator.
	 *
	 * @var string|null
	 */
	private ?string $written = null;

	/**
	 * Builds a migrator over in-memory storage.
	 *
	 * @param string   $code_version Code version.
	 * @param string   $stored       Stored schema version.
	 * @param string[] $versions     Versions that have a migration, in any order.
	 */
	private function migrator( string $code_version, string $stored, array $versions ): Migrator {
		$migrations = array();
		foreach ( $versions as $version ) {
			$migrations[ $version ] = function () use ( $version ): void {
				$this->ran[] = $version;
			};
		}
		return new Migrator(
			$code_version,
			static fn (): string => $stored,
			function ( string $version ): void {
				$this->written = $version;
			},
			$migrations
		);
	}

	public function test_runs_pending_migrations_in_version_order(): void {
		$applied = $this->migrator( '0.4.0', '0.1.0', array( '0.3.0', '0.10.0', '0.2.0' ) )->run();

		$this->assertSame( array( '0.2.0', '0.3.0' ), $this->ran );
		$this->assertSame( array( '0.2.0', '0.3.0' ), $applied );
		$this->assertSame( '0.4.0', $this->written );
	}

	public function test_skips_migrations_already_applied(): void {
		$this->migrator( '0.3.0', '0.2.0', array( '0.1.0', '0.2.0', '0.3.0' ) )->run();

		$this->assertSame( array( '0.3.0' ), $this->ran );
	}

	public function test_does_nothing_when_schema_is_current(): void {
		$applied = $this->migrator( '0.2.0', '0.2.0', array( '0.2.0' ) )->run();

		$this->assertSame( array(), $applied );
		$this->assertSame( array(), $this->ran );
		$this->assertNull( $this->written );
	}

	public function test_does_not_downgrade_when_stored_version_is_newer(): void {
		$this->migrator( '0.2.0', '0.5.0', array( '0.3.0' ) )->run();

		$this->assertSame( array(), $this->ran );
		$this->assertNull( $this->written );
	}

	public function test_records_version_on_first_install_without_migrations(): void {
		$this->migrator( '0.1.0', '0.0.0', array() )->run();

		$this->assertSame( '0.1.0', $this->written );
	}
}
