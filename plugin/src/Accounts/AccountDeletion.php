<?php
/**
 * Logical account deletion.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

use EuTeAjudo\Accounts\Google\GoogleAuth;
use EuTeAjudo\Agents\AgentWork;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType;

/**
 * Deletes an account logically (MVP decision): the email is replaced by an invalid, unique address
 * and only a salted hash of it is kept; the Google link, the role, the password and every session
 * go away. The alias stays, so what the person wrote keeps its author label. The same email can
 * start a new account, which gets a new alias with no link to the old one.
 */
final class AccountDeletion {

	public const DELETED_META    = '_eta_deleted_at';
	public const EMAIL_HASH_META = '_eta_deleted_email_hash';

	/**
	 * Deletes an account.
	 *
	 * @param int $user_id User ID.
	 */
	public static function delete( int $user_id ): void {
		$user = get_userdata( $user_id );
		if ( ! $user instanceof \WP_User || self::is_deleted( $user_id ) ) {
			return;
		}
		$hash = hash_hmac( 'sha256', strtolower( $user->user_email ), wp_salt( 'auth' ) );

		update_user_meta( $user_id, self::EMAIL_HASH_META, $hash );
		update_user_meta( $user_id, self::DELETED_META, time() );
		delete_user_meta( $user_id, GoogleAuth::SUBJECT_META );
		delete_user_meta( $user_id, EmailConfirmation::TOKEN_META );

		$address = $user->user_email;
		// Core would announce an "email changed" and a "password changed" here, with the
		// replacement address. The person only needs to know the account is gone.
		add_filter( 'send_email_change_email', '__return_false' );
		add_filter( 'send_password_change_email', '__return_false' );
		wp_update_user(
			array(
				'ID'         => $user_id,
				'user_email' => 'deleted-' . substr( $hash, 0, 24 ) . '@euteajudo.invalid',
				'user_pass'  => wp_generate_password( 64, true, true ),
			)
		);
		remove_filter( 'send_email_change_email', '__return_false' );
		remove_filter( 'send_password_change_email', '__return_false' );
		$user->set_role( '' );
		\WP_Session_Tokens::get_instance( $user_id )->destroy_all();
		self::close_requests( $user_id );
		if ( is_email( $address ) ) {
			wp_mail(
				$address,
				__( 'EuTeAjudo: your account was deleted', 'euteajudo' ),
				__( "Hi.\n\nYour account at EuTeAjudo.org was deleted, as you asked.\n\nEuTeAjudo.org", 'euteajudo' )
			);
		}
	}

	/**
	 * Closes the person's open requests, so no one keeps replying to an account that is gone.
	 *
	 * @param int $user_id User ID.
	 */
	private static function close_requests( int $user_id ): void {
		$open = get_posts(
			array(
				'post_type'   => HelpRequestType::POST_TYPE,
				'post_status' => AgentWork::OPEN_STATUSES,
				'author'      => $user_id,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		foreach ( $open as $request_id ) {
			HelpRequests::set_status( (int) $request_id, HelpRequestType::CLOSED );
			update_post_meta( (int) $request_id, HelpRequests::CLOSED_REASON_META, 'account_deleted' );
			update_post_meta( (int) $request_id, HelpRequests::CLOSED_AT_META, time() );
			update_post_meta( (int) $request_id, HelpRequests::CLOSED_BY_META, 'system' );
		}
	}

	/**
	 * Whether an account was deleted.
	 *
	 * @param int $user_id User ID.
	 */
	public static function is_deleted( int $user_id ): bool {
		return (int) get_user_meta( $user_id, self::DELETED_META, true ) > 0;
	}
}
