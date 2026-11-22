<?php
/**
 * Forgot password block.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	if ( 'reset_sent' === Messages::current_step() ) {
		echo View::notice( __( 'If this email has an account, we sent a link to create a new password. Check your inbox and the spam folder.', 'euteajudo' ) );
	}
	echo View::error_notice();
	// phpcs:enable
	?>
	<p class="eta-lead"><?php esc_html_e( 'Type the email of your account. If it is registered, we send a link to create a new password.', 'euteajudo' ); ?></p>
	<?php echo View::open( 'password_request' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
		echo View::email( 'eta-password-request-email' );
		echo View::submit( __( 'Send link', 'euteajudo' ) );
		// phpcs:enable
		?>
	</form>
	<p class="eta-note"><?php esc_html_e( 'Account created with Google? Go back and use "Continue with Google".', 'euteajudo' ); ?></p>
	<?php echo View::links( array( Pages::url( Pages::SIGN_IN ) => __( 'Back to sign in', 'euteajudo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
</div>
