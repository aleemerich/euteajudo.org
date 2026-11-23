<?php
/**
 * No avatars.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Privacy;

/**
 * Avatars are off everywhere. Gravatar URLs carry a hash of the email, which can identify a person,
 * so no avatar URL is ever produced, not even in REST responses.
 */
final class Avatars {

	/**
	 * Hooks the filters.
	 */
	public static function register(): void {
		add_filter( 'option_show_avatars', '__return_false' );
		add_filter( 'pre_get_avatar_data', array( self::class, 'empty_avatar_data' ) );
	}

	/**
	 * Short-circuits avatar lookups with an empty URL.
	 *
	 * @param array<string, mixed> $args Avatar arguments.
	 * @return array<string, mixed>
	 */
	public static function empty_avatar_data( array $args ): array {
		$args['url']          = '';
		$args['found_avatar'] = false;
		return $args;
	}
}
