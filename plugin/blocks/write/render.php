<?php
/**
 * Write a help request.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\FormState;
use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View as FormView;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\View;

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account eta-app' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the view helpers.
	echo FormView::error_notice();
	if ( 'welcome' === Messages::current_step() ) {
		echo FormView::notice( __( 'Your email is confirmed. Whenever you are ready, write here what is happening.', 'euteajudo' ) );
	}
	echo FormView::open( 'write' );
	// phpcs:enable
	?>
		<label for="eta-write-title"><?php esc_html_e( 'Title', 'euteajudo' ); ?> <span class="eta-hint-inline"><?php esc_html_e( 'so you can find it later', 'euteajudo' ); ?></span></label>
		<input id="eta-write-title" name="title" type="text" maxlength="<?php echo (int) HelpRequests::MAX_TITLE; ?>" required value="<?php echo esc_attr( FormState::value( 'title' ) ); ?>"<?php echo FormView::invalid_attr( 'title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static attribute. ?>>
		<label for="eta-write-body"><?php esc_html_e( 'What is happening?', 'euteajudo' ); ?></label>
		<p class="eta-hint eta-hint-before" id="eta-write-guides"><?php esc_html_e( 'If it helps, think about how long this has been going on, what you have already tried and what you feel you need now. Write your way; nothing needs to be organised.', 'euteajudo' ); ?></p>
		<textarea id="eta-write-body" name="body" rows="10" required aria-describedby="eta-write-guides"<?php echo FormView::invalid_attr( 'body' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static attribute. ?>><?php echo esc_textarea( FormState::value( 'body' ) ); ?></textarea>
		<p class="eta-note"><?php esc_html_e( 'Phone numbers, emails and documents are removed before saving. The reply may take a few hours; we let you know by email.', 'euteajudo' ); ?></p>
		<button class="eta-button wp-element-button" type="submit"><?php esc_html_e( 'Send request', 'euteajudo' ); ?></button>
	</form>
	<?php echo View::disclosure(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
</div>
