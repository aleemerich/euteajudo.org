<?php
/**
 * Bootstrap for integration tests: loads the WordPress test suite and the plugin.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

require dirname( __DIR__ ) . '/vendor/autoload.php';

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );

$euteajudo_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
require $euteajudo_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/plugin/euteajudo.php';
	}
);

require $euteajudo_tests_dir . '/includes/bootstrap.php';
