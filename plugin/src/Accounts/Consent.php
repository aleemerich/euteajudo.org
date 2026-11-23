<?php
/**
 * Age declaration and consent records.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Records, per account, that the person declared being 18 or older and accepted the consent text
 * (processing of what they write and transfer to AI providers abroad, LGPD arts. 11 and 33).
 */
final class Consent {

	/**
	 * Version of the consent text. Change it whenever the text changes, so new acceptances can
	 * be told apart from old ones.
	 */
	public const VERSION = '2026-11-23';

	public const META = '_eta_consent';

	/**
	 * Stores the acceptance of the current consent version and the age declaration.
	 *
	 * @param int $user_id User ID.
	 * @param int $now     Unix time of the acceptance.
	 */
	public static function record( int $user_id, int $now ): void {
		update_user_meta(
			$user_id,
			self::META,
			array(
				'version'     => self::VERSION,
				'accepted_at' => $now,
				'adult_at'    => $now,
			)
		);
	}

	/**
	 * Consent version accepted by a user, or an empty string.
	 *
	 * @param int $user_id User ID.
	 */
	public static function version_of( int $user_id ): string {
		$record = get_user_meta( $user_id, self::META, true );
		return is_array( $record ) && is_string( $record['version'] ?? null ) ? $record['version'] : '';
	}
}
