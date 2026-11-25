<?php
/**
 * Title: Home
 * Slug: euteajudo/home
 * Categories: euteajudo
 * Inserter: no
 * Description: Content of the Home page: welcome, the lit window, how it works and help right now. The theme creates the Home page from it once; after that, the page is edited in Pages.
 *
 * @package EuTeAjudo
 */

$euteajudo_link = static fn ( string $path ): string => esc_url( home_url( $path ) );
?>
<!-- wp:group {"tagName":"section","className":"eta-hero"} -->
<section class="wp-block-group eta-hero"><!-- wp:heading {"level":1,"className":"eta-display"} -->
<h1 class="wp-block-heading eta-display"><?php echo esc_html__( 'There is a light on here.', 'euteajudo-theme' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"eta-lede"} -->
<p class="eta-lede"><?php echo esc_html__( 'Write at any time, your way, without saying who you are. What you write is read with care.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"eta-cta"} -->
<div class="wp-block-buttons eta-cta"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $euteajudo_link( '/criar-conta/' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the closure. ?>"><?php echo esc_html__( 'Start writing', 'euteajudo-theme' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-link"} -->
<div class="wp-block-button is-style-link"><a class="wp-block-button__link wp-element-button" href="<?php echo $euteajudo_link( '/entrar/' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the closure. ?>"><?php echo esc_html__( 'I already have an account', 'euteajudo-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"euteajudo/lit-window"} /-->

<!-- wp:group {"tagName":"section","className":"eta-how"} -->
<section class="wp-block-group eta-how"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html__( 'How it works', 'euteajudo-theme' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"eta-steps"} -->
<div class="wp-block-columns eta-steps"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html__( 'Create an account with just an email.', 'euteajudo-theme' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'You do not need to give your name. Here we call you Friend, with a number that is only yours, like Friend 48213.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html__( 'Tell what is happening.', 'euteajudo-theme' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Your way, in your time. Nothing needs to be organised.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html__( 'Get a reply written with care.', 'euteajudo-theme' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Replies are written by an artificial intelligence made to welcome people: it reads with care, does not judge and follows rules of respect. When a reply arrives, we let you know by email, without showing what you wrote.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph -->
<p><a href="<?php echo $euteajudo_link( '/sobre/' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the closure. ?>"><?php echo esc_html__( 'More about how it works', 'euteajudo-theme' ); ?></a></p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"eta-urgent"} -->
<section class="wp-block-group eta-urgent"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html__( 'If things are very hard right now', 'euteajudo-theme' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html__( 'CVV talks with you for free, at any time, by phone at 188 or by chat at cvv.org.br.', 'euteajudo-theme' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="tel:188"><?php echo esc_html__( 'Call 188', 'euteajudo-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
