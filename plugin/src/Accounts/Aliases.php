<?php
/**
 * Aliases: the only public identity of an account.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Gives every account an alias and keeps names out of user records.
 *
 * The alias number lives in user meta; display_name, nickname and user_nicename are always derived
 * from it, and first_name/last_name are always empty, whatever path created or updated the user.
 * For Friends and Agents the login is the alias too ("amigo-48213"); people who manage the site
 * (anyone who can edit posts) keep the login they chose.
 */
final class Aliases {

	public const META = '_eta_alias';

	/**
	 * Hooks into user creation and updates.
	 */
	public static function register(): void {
		add_action( 'user_register', array( self::class, 'assign' ), 1 );
		add_filter( 'wp_pre_insert_user_data', array( self::class, 'filter_user_data' ), 10, 3 );
		add_filter( 'insert_user_meta', array( self::class, 'filter_user_meta' ), 10, 3 );
	}

	/**
	 * Public label for an alias number, e.g. "Amigo 48213" in pt_BR.
	 *
	 * @param string $number Alias number.
	 */
	public static function label( string $number ): string {
		/* translators: %s: five-digit alias number. */
		return sprintf( __( 'Friend %s', 'euteajudo' ), $number );
	}

	/**
	 * Label as stored in user records: always in the site language, whatever language the
	 * current request runs in (a visitor's browser, an administrator's own locale, WP-CLI).
	 *
	 * @param string $number Alias number.
	 */
	private static function stored_label( string $number ): string {
		$switched = switch_to_locale( get_locale() );
		$label    = self::label( $number );
		if ( $switched ) {
			restore_previous_locale();
		}
		return $label;
	}

	/**
	 * URL-safe form of the alias in the site language ("amigo-48213"), used as login and nicename.
	 *
	 * @param string $number Alias number.
	 */
	private static function slug( string $number ): string {
		return sanitize_title( self::stored_label( $number ) );
	}

	/**
	 * Alias number of a user, or an empty string when none was assigned yet.
	 *
	 * @param int $user_id User ID.
	 */
	public static function number_of( int $user_id ): string {
		return (string) get_user_meta( $user_id, self::META, true );
	}

	/**
	 * Assigns an alias to a user that has none, and rewrites the public name fields.
	 *
	 * @param int $user_id User ID.
	 */
	public static function assign( int $user_id ): void {
		if ( '' === self::number_of( $user_id ) ) {
			update_user_meta( $user_id, self::META, self::draw() );
		}

		// Run through wp_update_user so the filters below normalise every name field.
		wp_update_user( array( 'ID' => $user_id ) );
		self::sync_login( $user_id );
	}

	/**
	 * Makes the login of a Friend or Agent equal to its alias slug.
	 *
	 * WordPress cannot change user_login through its user API, so this writes the column directly,
	 * and only when no other account already has that login.
	 *
	 * @param int $user_id User ID.
	 */
	public static function sync_login( int $user_id ): void {
		$user   = get_userdata( $user_id );
		$number = self::number_of( $user_id );
		if ( ! $user instanceof \WP_User || '' === $number || user_can( $user, 'edit_posts' ) ) {
			return;
		}
		$login = self::slug( $number );
		if ( $user->user_login === $login ) {
			return;
		}
		$owner = username_exists( $login );
		if ( false !== $owner && $owner !== $user_id ) {
			return;
		}
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_login' => $login ), array( 'ID' => $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- The user API cannot change user_login; cache cleared below.
		clean_user_cache( $user_id );
	}

	/**
	 * Rewrites the public name fields of every account from its alias, in the site language.
	 */
	public static function refresh_all(): void {
		$user_ids = get_users(
			array(
				'fields'       => 'ID',
				'meta_key'     => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off migration.
				'meta_compare' => 'EXISTS',
			)
		);
		foreach ( $user_ids as $user_id ) {
			wp_update_user( array( 'ID' => (int) $user_id ) );
			self::sync_login( (int) $user_id );
		}
	}

	/**
	 * Gives an alias to every account that has none (accounts created before the plugin).
	 */
	public static function backfill(): void {
		$user_ids = get_users(
			array(
				'fields'       => 'ID',
				'meta_key'     => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off migration.
				'meta_compare' => 'NOT EXISTS',
			)
		);
		foreach ( $user_ids as $user_id ) {
			self::assign( (int) $user_id );
		}
	}

	/**
	 * Forces display_name and user_nicename to the alias on updates.
	 *
	 * @param array<string, mixed> $data    User data about to be saved.
	 * @param bool                 $update  Whether this is an update.
	 * @param int|null             $user_id User ID on update.
	 * @return array<string, mixed>
	 */
	public static function filter_user_data( array $data, bool $update, ?int $user_id ): array {
		$number = ( $update && $user_id ) ? self::number_of( $user_id ) : '';
		if ( '' === $number ) {
			// New accounts get a neutral placeholder until user_register assigns the alias.
			$data['display_name']  = '';
			$data['user_nicename'] = 'friend-pending-' . wp_generate_password( 8, false );
			return $data;
		}
		$data['display_name']  = self::stored_label( $number );
		$data['user_nicename'] = self::slug( $number );
		return $data;
	}

	/**
	 * Forces nickname to the alias and empties first and last name.
	 *
	 * @param array<string, mixed> $meta   User meta about to be saved.
	 * @param \WP_User             $user   User object.
	 * @param bool                 $update Whether this is an update.
	 * @return array<string, mixed>
	 */
	public static function filter_user_meta( array $meta, \WP_User $user, bool $update ): array {
		$number             = $update ? self::number_of( $user->ID ) : '';
		$meta['nickname']   = '' === $number ? '' : self::stored_label( $number );
		$meta['first_name'] = '';
		$meta['last_name']  = '';
		return $meta;
	}

	/**
	 * A free alias number (not used by anyone nor in the Agent alias bank).
	 */
	public static function free_number(): string {
		return self::draw();
	}

	/**
	 * Draws a free alias number, widening the range by one digit when it is full.
	 *
	 * @throws AliasSpaceExhausted When the range is full and already at the maximum width.
	 */
	private static function draw(): string {
		$range = AliasRange::current();
		while ( true ) {
			try {
				return self::generator( $range )->generate();
			} catch ( AliasSpaceExhausted $full ) {
				$wider = $range->widened();
				if ( null === $wider ) {
					throw $full;
				}
				update_option( AliasRange::OPTION, $wider->to_option(), false );
				self::notify_widening( $range, $wider );
				$range = $wider;
			}
		}
	}

	/**
	 * Tells the administrator that the alias range grew.
	 *
	 * @param AliasRange $from Previous range.
	 * @param AliasRange $to   New range.
	 */
	private static function notify_widening( AliasRange $from, AliasRange $to ): void {
		wp_mail(
			(string) get_option( 'admin_email' ),
			__( 'EuTeAjudo: alias range widened', 'euteajudo' ),
			sprintf(
				/* translators: 1: previous maximum digits, 2: new maximum digits. */
				__( 'Aliases with up to %1$d digits were almost all taken, so new aliases can now have up to %2$d digits. Nothing else needs to be done.', 'euteajudo' ),
				$from->max_digits,
				$to->max_digits
			)
		);
	}

	/**
	 * Generator wired to WordPress storage.
	 *
	 * @param AliasRange $range Numbers an alias may take.
	 */
	private static function generator( AliasRange $range ): AliasGenerator {
		return new AliasGenerator(
			static fn ( int $min, int $max ): int => random_int( $min, $max ),
			static fn ( string $number ): bool => \EuTeAjudo\Agents\AgentAliases::contains( $number ) || array() !== get_users(
				array(
					'fields'     => 'ID',
					'number'     => 1,
					'meta_key'   => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Uniqueness check on registration only.
					'meta_value' => $number, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Same.
				)
			),
			$range
		);
	}
}
