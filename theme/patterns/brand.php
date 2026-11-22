<?php
/**
 * Title: Brand
 * Slug: euteajudo/brand
 * Inserter: no
 * Description: Logo linking to the home page. The SVG uses currentColor, so it follows the text color in Night and Day.
 *
 * @package EuTeAjudo
 */

$euteajudo_logo = file_get_contents( get_theme_file_path( 'assets/images/logo-mono.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
?>
<!-- wp:html -->
<a class="eta-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'EuTeAjudo.org, home', 'euteajudo-theme' ); ?>"><?php echo $euteajudo_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted SVG shipped with the theme. ?></a>
<!-- /wp:html -->
