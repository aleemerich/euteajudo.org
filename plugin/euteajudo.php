<?php
/**
 * Plugin Name:       EuTeAjudo
 * Plugin URI:        https://euteajudo.org
 * Description:       Anonymous help requests answered by support agents.
 * Version:           0.1.0
 * Requires at least: 7.1
 * Requires PHP:      8.5
 * Author:            EuTeAjudo.org
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       euteajudo
 * Domain Path:       /languages
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

defined( 'ABSPATH' ) || exit;

const VERSION     = '0.1.0';
const PLUGIN_FILE = __FILE__;

/*
 * PSR-4 autoloader for the EuTeAjudo\ namespace. Kept inside the plugin so a deploy is a plain
 * file copy (FTPS) with no Composer step on the server.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = __NAMESPACE__ . '\\';
		if ( ! str_starts_with( $class_name, $prefix ) ) {
			return;
		}
		$file = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

add_action( 'plugins_loaded', array( Plugin::class, 'boot' ) );
