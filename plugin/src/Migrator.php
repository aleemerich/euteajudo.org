<?php
/**
 * Version-based migrations.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

/**
 * Runs pending migrations in version order and records the new schema version.
 *
 * Pure PHP: storage is injected, so the ordering rules are unit-testable without WordPress.
 */
final class Migrator {

	/**
	 * Creates a migrator.
	 *
	 * @param string   $code_version Version of the code being loaded.
	 * @param \Closure $read_stored  Returns the schema version already applied.
	 * @param \Closure $write_stored Persists the schema version after migrating.
	 * @param array    $migrations   Migrations keyed by the version that introduces them.
	 *
	 * @phpstan-param \Closure(): string $read_stored
	 * @phpstan-param \Closure(string): void $write_stored
	 * @phpstan-param array<string, callable(): void> $migrations
	 */
	public function __construct(
		private readonly string $code_version,
		private readonly \Closure $read_stored,
		private readonly \Closure $write_stored,
		private readonly array $migrations,
	) {}

	/**
	 * Applies every migration newer than the stored version and not newer than the code version.
	 *
	 * @return list<string> Versions whose migrations ran, in order.
	 */
	public function run(): array {
		$stored = ( $this->read_stored )();
		if ( version_compare( $stored, $this->code_version, '>=' ) ) {
			return array();
		}

		$pending = array_filter(
			array_keys( $this->migrations ),
			fn ( string $version ): bool => version_compare( $version, $stored, '>' )
				&& version_compare( $version, $this->code_version, '<=' )
		);
		usort( $pending, static fn ( string $a, string $b ): int => version_compare( $a, $b ) );

		foreach ( $pending as $version ) {
			( $this->migrations[ $version ] )();
		}

		( $this->write_stored )( $this->code_version );

		return $pending;
	}
}
