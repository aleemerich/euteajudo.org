<?php
/**
 * Sign-up form block, with the "check your email" and "under 18" steps.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;

wp_enqueue_script( 'euteajudo-accounts' );
$euteajudo_step    = Messages::current_step();
$euteajudo_privacy = get_privacy_policy_url();
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
<?php if ( 'minor' === $euteajudo_step ) : ?>
	<h2><?php esc_html_e( 'This space is for people 18 and over', 'euteajudo' ); ?></h2>
	<p><?php esc_html_e( 'You deserve to be heard now. These services help people under 18, for free and with trained people:', 'euteajudo' ); ?></p>
	<div class="eta-services">
		<div class="eta-service">
			<strong><?php esc_html_e( 'Pode Falar (UNICEF)', 'euteajudo' ); ?></strong>
			<p><?php esc_html_e( 'For people aged 13 to 24, on WhatsApp.', 'euteajudo' ); ?></p>
			<a class="eta-button wp-element-button" href="https://podefalar.org.br/"><?php esc_html_e( 'Open Pode Falar', 'euteajudo' ); ?></a>
		</div>
		<div class="eta-service">
			<strong><?php esc_html_e( 'CVV', 'euteajudo' ); ?></strong>
			<p><?php esc_html_e( 'Any age, any time, by phone at 188 or by chat.', 'euteajudo' ); ?></p>
			<a class="eta-button eta-button-quiet wp-element-button" href="tel:188"><?php esc_html_e( 'Call 188', 'euteajudo' ); ?></a>
		</div>
	</div>
<?php elseif ( 'check_email' === $euteajudo_step ) : ?>
	<div class="eta-window" aria-hidden="true"><span></span></div>
	<h2><?php esc_html_e( 'Almost there', 'euteajudo' ); ?></h2>
	<p><?php esc_html_e( 'We sent a link to the email you typed. Open the email and tap the link to confirm it. The link is valid for 48 hours.', 'euteajudo' ); ?></p>
	<p class="eta-note"><?php esc_html_e( 'The subject is "EuTeAjudo: confirm your email". If it does not arrive in a few minutes, check the spam folder. To get a new link, try to sign in.', 'euteajudo' ); ?></p>
	<?php echo View::links( array( Pages::url( Pages::SIGN_IN ) => __( 'Sign in', 'euteajudo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
<?php else : ?>
	<?php echo View::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
	<p class="eta-lead"><?php esc_html_e( 'Here you are just a Friend with a number, like Friend 48213. Nobody sees your email.', 'euteajudo' ); ?></p>
	<?php echo View::open( 'sign_up' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<?php echo View::default_submit(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<fieldset class="eta-before">
			<legend><?php esc_html_e( 'Before you start', 'euteajudo' ); ?></legend>
			<label class="eta-choice"><input type="radio" name="age" value="adult"> <?php esc_html_e( 'I am 18 or older', 'euteajudo' ); ?></label>
			<label class="eta-choice"><input type="radio" name="age" value="minor"> <?php esc_html_e( 'I am under 18', 'euteajudo' ); ?></label>
			<label class="eta-choice"><input type="checkbox" name="consent" value="1"> <span>
				<?php esc_html_e( 'I agree that what I write is read by an artificial intelligence made to welcome people, including services outside Brazil.', 'euteajudo' ); ?>
				<?php
				if ( '' !== $euteajudo_privacy ) {
					printf(
						/* translators: %s: link to the privacy policy. */
						esc_html__( 'I have read the %s.', 'euteajudo' ),
						'<a href="' . esc_url( $euteajudo_privacy ) . '">' . esc_html__( 'Privacy policy', 'euteajudo' ) . '</a>'
					);
				}
				?>
			</span></label>
		</fieldset>
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
		echo View::google_button();
		echo View::divider();
		echo View::email( 'eta-sign-up-email' );
		echo View::password( 'eta-sign-up-password', __( 'Password', 'euteajudo' ), 'new-password', __( 'At least 10 characters.', 'euteajudo' ) );
		echo View::submit( __( 'Create account', 'euteajudo' ), 'email' );
		// phpcs:enable
		?>
	</form>
	<?php echo View::links( array( Pages::url( Pages::SIGN_IN ) => __( 'Already have an account? Sign in', 'euteajudo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
<?php endif; ?>
</div>
