<?php
/**
 * Email confirmation result block.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
<?php if ( 'email_confirmed' === Messages::current_step() && is_user_logged_in() ) : ?>
	<div class="eta-window" aria-hidden="true"><span></span></div>
	<p>
		<?php
		printf(
			/* translators: %s: alias label, e.g. "Friend 48213". */
			esc_html__( 'Email confirmed. You are signed in as %s.', 'euteajudo' ),
			esc_html( Aliases::label( Aliases::number_of( get_current_user_id() ) ) )
		);
		?>
	</p>
	<a class="eta-button wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the home page', 'euteajudo' ); ?></a>
<?php elseif ( '' !== Messages::current_error() ) : ?>
	<?php echo View::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
	<?php echo View::links( array( Pages::url( Pages::SIGN_IN ) => __( 'Sign in', 'euteajudo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
<?php else : ?>
	<p><?php esc_html_e( 'Open the link we sent to your email to confirm it.', 'euteajudo' ); ?></p>
<?php endif; ?>
</div>
