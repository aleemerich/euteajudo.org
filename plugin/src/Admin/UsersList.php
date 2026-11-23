<?php
/**
 * Users screen in wp-admin.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Admin;

use EuTeAjudo\Accounts\Aliases;

/**
 * Shows the alias on the users screen, lets administrators sort by it and find an account by its
 * alias number. The "Name" column goes away: names are never stored, so it was always empty.
 */
final class UsersList {

	public const COLUMN = 'eta_alias';

	/**
	 * Hooks the screen filters.
	 */
	public static function register(): void {
		add_filter( 'manage_users_columns', array( self::class, 'columns' ) );
		add_filter( 'manage_users_custom_column', array( self::class, 'column_value' ), 10, 3 );
		add_filter( 'manage_users_sortable_columns', array( self::class, 'sortable_columns' ) );
		add_action( 'pre_get_users', array( self::class, 'adjust_query' ) );
	}

	/**
	 * Puts the alias column where the name column was.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public static function columns( array $columns ): array {
		$result = array();
		foreach ( $columns as $key => $label ) {
			if ( 'name' === $key ) {
				continue;
			}
			$result[ $key ] = $label;
			if ( 'username' === $key ) {
				$result[ self::COLUMN ] = __( 'Alias', 'euteajudo' );
			}
		}
		return $result;
	}

	/**
	 * Prints the alias label in the alias column.
	 *
	 * @param string $output  Current output.
	 * @param string $column  Column key.
	 * @param int    $user_id User ID.
	 */
	public static function column_value( string $output, string $column, int $user_id ): string {
		if ( self::COLUMN !== $column ) {
			return $output;
		}
		$number = Aliases::number_of( $user_id );
		return '' === $number ? '&mdash;' : esc_html( Aliases::label( $number ) );
	}

	/**
	 * Makes the alias column sortable.
	 *
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public static function sortable_columns( array $columns ): array {
		$columns[ self::COLUMN ] = self::COLUMN;
		return $columns;
	}

	/**
	 * In wp-admin, sorts by alias and turns a numeric search into an alias lookup (WordPress
	 * would otherwise search user IDs).
	 *
	 * @param \WP_User_Query $query User query.
	 */
	public static function adjust_query( \WP_User_Query $query ): void {
		if ( ! is_admin() ) {
			return;
		}

		if ( self::COLUMN === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', Aliases::META );
			$query->set( 'orderby', 'meta_value_num' );
		}

		$search = trim( (string) $query->get( 'search' ), '* ' );
		if ( '' !== $search && ctype_digit( $search ) ) {
			$query->set( 'search', '' );
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => Aliases::META,
						'value' => $search,
					),
				)
			);
		}
	}
}
