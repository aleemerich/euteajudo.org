<?php
/**
 * New password block (from the emailed link).
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\View;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;

wp_enqueue_script( 'euteajudo-accounts' );
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- The reset key is the proof; checked by the handler.
$euteajudo_login = isset( $_GET['login'] ) ? sanitize_user( wp_unslash( $_GET['login'] ) ) : '';
$euteajudo_key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
// phpcs:enable
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
<?php if ( '' === $euteajudo_login || '' === $euteajudo_key ) : ?>
	<?php echo View::notice( __( 'Open this page from the link we sent to your email.', 'euteajudo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
	<?php echo View::links( array( Pages::url( Pages::PASSWORD_REQUEST ) => __( 'Ask for a new link', 'euteajudo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
<?php else : ?>
	<?php echo View::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
	<?php echo View::open( 'password_reset' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<input type="hidden" name="login" value="<?php echo esc_attr( $euteajudo_login ); ?>">
		<input type="hidden" name="key" value="<?php echo esc_attr( $euteajudo_key ); ?>">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
		echo View::password( 'eta-new-password', __( 'New password', 'euteajudo' ), 'new-password', sprintf( /* translators: %d: minimum number of characters. */ __( 'At least %d characters.', 'euteajudo' ), \EuTeAjudo\Accounts\Forms\SignUp::MIN_PASSWORD ) );
		echo View::submit( __( 'Save new password', 'euteajudo' ) );
		// phpcs:enable
		?>
	</form>
<?php endif; ?>
</div>
