<?php
/**
 * Sign-in form block.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;

wp_enqueue_script( 'euteajudo-accounts' );
$euteajudo_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Validated by the handler.
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<?php
	if ( 'password_changed' === Messages::current_step() ) {
		echo View::notice( __( 'Your password was changed. Sign in with the new password.', 'euteajudo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	}
	if ( 'account_deleted' === Messages::current_step() ) {
		echo View::notice( __( 'Your account was deleted.', 'euteajudo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	}
	echo View::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	?>
	<p class="eta-lead"><?php esc_html_e( 'Good to have you back.', 'euteajudo' ); ?></p>
	<?php echo View::open( 'sign_in' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $euteajudo_redirect ); ?>">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
		echo View::default_submit();
		echo View::google_button();
		echo View::divider();
		echo View::email( 'eta-sign-in-email' );
		echo View::password( 'eta-sign-in-password', __( 'Password', 'euteajudo' ), 'current-password' );
		echo View::submit( __( 'Sign in', 'euteajudo' ), 'email' );
		// phpcs:enable
		?>
	</form>
	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	echo View::links(
		array(
			Pages::url( Pages::PASSWORD_REQUEST ) => __( 'Forgot password', 'euteajudo' ),
			Pages::url( Pages::SIGN_UP )          => __( "Don't have an account yet? Create account", 'euteajudo' ),
		)
	);
	// phpcs:enable
	?>
</div>
