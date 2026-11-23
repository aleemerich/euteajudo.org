<?php
/**
 * "My account" form handlers.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\Pages;

/**
 * Account deletion requested by the person themself.
 */
final class AccountForm {

	/**
	 * Deletes the current account after explicit confirmation.
	 *
	 * @param array<string, string> $input Submitted fields: confirm.
	 */
	public static function delete( array $input ): string {
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			return Pages::url( Pages::SIGN_IN );
		}
		if ( user_can( $user, 'edit_posts' ) ) {
			return Pages::url( Pages::ACCOUNT, array( 'eta_auth_error' => 'delete_not_allowed' ) );
		}
		if ( '1' !== ( $input['confirm'] ?? '' ) ) {
			return Pages::url( Pages::ACCOUNT, array( 'eta_auth_error' => 'delete_unconfirmed' ) );
		}

		AccountDeletion::delete( $user->ID );
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );

		return Pages::url( Pages::SIGN_IN, array( 'eta_step' => 'account_deleted' ) );
	}
}
