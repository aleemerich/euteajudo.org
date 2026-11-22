<?php
/**
 * Header account link block.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account-link' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
<?php if ( is_user_logged_in() ) : ?>
	<span class="eta-alias"><?php echo esc_html( Aliases::label( Aliases::number_of( get_current_user_id() ) ) ); ?></span>
	<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign out', 'euteajudo' ); ?></a>
<?php else : ?>
	<a href="<?php echo esc_url( Pages::url( Pages::SIGN_IN ) ); ?>"><?php esc_html_e( 'Sign in', 'euteajudo' ); ?></a>
<?php endif; ?>
</div>
