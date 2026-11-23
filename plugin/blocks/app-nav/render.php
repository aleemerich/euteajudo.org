<?php
/**
 * Friend navigation: Write, My requests, Account.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Pages;

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	return;
}
$euteajudo_items = array(
	Pages::WRITE       => array( __( 'Write', 'euteajudo' ), '<path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="M13.5 6.5l4 4"/>' ),
	Pages::MY_REQUESTS => array( __( 'My requests', 'euteajudo' ), '<path d="M8 6h12M8 12h12M8 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>' ),
	Pages::ACCOUNT     => array( __( 'Account', 'euteajudo' ), '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>' ),
);
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-app-nav' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?> aria-label="<?php esc_attr_e( 'Main', 'euteajudo' ); ?>">
	<?php foreach ( $euteajudo_items as $euteajudo_key => [ $euteajudo_label, $euteajudo_icon ] ) : ?>
		<a href="<?php echo esc_url( Pages::url( $euteajudo_key ) ); ?>"<?php echo Pages::is_current( array( $euteajudo_key ) ) ? ' aria-current="page"' : ''; ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo $euteajudo_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?></svg>
			<span><?php echo esc_html( $euteajudo_label ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
