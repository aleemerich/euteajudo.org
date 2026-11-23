<?php
/**
 * "My account" block: alias, email, appearance, sign out and deletion.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\Forms\View;

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	return;
}
wp_enqueue_script( 'euteajudo-accounts' );
$euteajudo_user = wp_get_current_user();
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<?php echo View::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
	<?php
	$euteajudo_lead = sprintf(
		/* translators: %s: alias label, e.g. "Friend 48213". */
		esc_html__( 'Here you are %s.', 'euteajudo' ),
		'<strong>' . esc_html( Aliases::label( Aliases::number_of( $euteajudo_user->ID ) ) ) . '</strong>'
	);
	?>
	<p class="eta-lead"><?php echo $euteajudo_lead; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></p>

	<h2><?php esc_html_e( 'Email', 'euteajudo' ); ?></h2>
	<p><?php echo esc_html( View::masked_email( $euteajudo_user->user_email ) ); ?></p>
	<p class="eta-note"><?php esc_html_e( 'Only you see this email. It is used to sign in and to tell you when a reply arrives.', 'euteajudo' ); ?></p>

	<fieldset class="eta-before eta-scheme-choice" hidden>
		<legend><?php esc_html_e( 'Appearance', 'euteajudo' ); ?></legend>
		<label class="eta-choice"><input type="radio" name="eta-scheme" value="auto"> <?php esc_html_e( 'Automatic (follows your device)', 'euteajudo' ); ?></label>
		<label class="eta-choice"><input type="radio" name="eta-scheme" value="night"> <?php esc_html_e( 'Night', 'euteajudo' ); ?></label>
		<label class="eta-choice"><input type="radio" name="eta-scheme" value="day"> <?php esc_html_e( 'Day', 'euteajudo' ); ?></label>
		<p class="eta-note"><?php esc_html_e( 'The change is applied right away and saved only in this browser.', 'euteajudo' ); ?></p>
		<p class="eta-saved" role="status" aria-live="polite" data-saved="<?php esc_attr_e( 'Appearance saved.', 'euteajudo' ); ?>"></p>
	</fieldset>

	<h2 class="eta-section"><?php esc_html_e( 'Sign out', 'euteajudo' ); ?></h2>
	<p><?php esc_html_e( 'If other people use this device, sign out when you finish.', 'euteajudo' ); ?> <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign out of this device', 'euteajudo' ); ?></a></p>

	<h2 class="eta-section"><?php esc_html_e( 'Delete account', 'euteajudo' ); ?></h2>
	<?php if ( user_can( $euteajudo_user, 'edit_posts' ) ) : ?>
		<p class="eta-note"><?php esc_html_e( 'Accounts that manage the site cannot be deleted here.', 'euteajudo' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Your email is removed from the site and nobody can sign in to this account anymore. What you wrote stays under your alias, without your email. You can create a new account later; it gets a new alias.', 'euteajudo' ); ?></p>
		<?php echo View::open( 'delete_account' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
			<label class="eta-choice"><input type="checkbox" name="confirm" value="1"> <?php esc_html_e( 'I understand and want to delete my account', 'euteajudo' ); ?></label>
			<button class="eta-button eta-button-quiet wp-element-button" type="submit"><?php esc_html_e( 'Delete my account', 'euteajudo' ); ?></button>
		</form>
	<?php endif; ?>
</div>
