<?php
/**
 * Footer links.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Site\ContentPages;

defined( 'ABSPATH' ) || exit;
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-site-links' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<nav aria-label="<?php esc_attr_e( 'About the site', 'euteajudo' ); ?>">
		<?php foreach ( ContentPages::KEYS as $euteajudo_key ) : ?>
			<?php $euteajudo_url = ContentPages::url( $euteajudo_key ); ?>
			<?php if ( '' !== $euteajudo_url ) : ?>
				<a href="<?php echo esc_url( $euteajudo_url ); ?>"><?php echo esc_html( ContentPages::title( $euteajudo_key ) ); ?></a>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<p><?php esc_html_e( 'At risk right now? Call 188 (CVV), free, at any time.', 'euteajudo' ); ?></p>
</div>
