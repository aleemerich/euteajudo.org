<?php
/**
 * Title: Footer links
 * Slug: euteajudo/footer-links
 * Categories: euteajudo
 * Inserter: no
 * Description: About, Terms of use and Privacy policy, and the crisis line.
 *
 * @package EuTeAjudo
 */

$euteajudo_links = array(
	'/sobre/'         => __( 'About', 'euteajudo-theme' ),
	'/termos-de-uso/' => __( 'Terms of use', 'euteajudo-theme' ),
	'/privacidade/'   => __( 'Privacy policy', 'euteajudo-theme' ),
);
?>
<!-- wp:html -->
<nav class="eta-site-links" aria-label="<?php echo esc_attr__( 'About the site', 'euteajudo-theme' ); ?>">
<?php foreach ( $euteajudo_links as $euteajudo_path => $euteajudo_label ) : ?>
<a href="<?php echo esc_url( home_url( $euteajudo_path ) ); ?>"><?php echo esc_html( $euteajudo_label ); ?></a>
<?php endforeach; ?>
</nav>
<!-- /wp:html -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'If things are hard right now, call 188 (CVV). It is free, at any time.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph -->
