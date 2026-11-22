<?php
/**
 * WordPress test suite configuration for the DDEV environment (and CI, which also runs DDEV).
 * Uses its own database: the suite drops and recreates every table in it.
 *
 * @package EuTeAjudo
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/wordpress/' );
define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );

define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'db' );
define( 'DB_PASSWORD', 'db' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required by the test suite.

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'EuTeAjudo tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
