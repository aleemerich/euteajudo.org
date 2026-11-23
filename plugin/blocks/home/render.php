<?php
/**
 * Public home.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Site\ContentPages;

defined( 'ABSPATH' ) || exit;

$euteajudo_signed_in = is_user_logged_in();
// Desktop layout options under review; ?inicio=rua or ?inicio=reorganizada, local site only.
$euteajudo_variant = 'local' === wp_get_environment_type() && isset( $_GET['inicio'] ) && 'reorganizada' === $_GET['inicio'] ? 'grid' : 'street'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preview switch.
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-home eta-home-' . $euteajudo_variant ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
	<section class="eta-hero">
		<div class="eta-hero-text">
			<h1 class="eta-display"><?php esc_html_e( 'There is a light on here.', 'euteajudo' ); ?></h1>
			<p class="eta-lede"><?php esc_html_e( 'Write at any time, your way. Nobody knows who you are, and your words will be read with care.', 'euteajudo' ); ?></p>
			<div class="eta-cta">
				<?php if ( $euteajudo_signed_in ) : ?>
					<a class="eta-button wp-element-button eta-button-inline" href="<?php echo esc_url( Pages::url( Pages::WRITE ) ); ?>"><?php esc_html_e( 'Start writing', 'euteajudo' ); ?></a>
					<a href="<?php echo esc_url( Pages::url( Pages::MY_REQUESTS ) ); ?>"><?php esc_html_e( 'My requests', 'euteajudo' ); ?></a>
				<?php else : ?>
					<a class="eta-button wp-element-button eta-button-inline" href="<?php echo esc_url( Pages::url( Pages::SIGN_UP ) ); ?>"><?php esc_html_e( 'Start writing', 'euteajudo' ); ?></a>
					<a href="<?php echo esc_url( Pages::url( Pages::SIGN_IN ) ); ?>"><?php esc_html_e( 'I already have an account', 'euteajudo' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="eta-facade" aria-hidden="true">
			<?php for ( $euteajudo_i = 0; $euteajudo_i < 12; $euteajudo_i++ ) : ?>
				<span class="eta-win<?php echo 6 === $euteajudo_i ? ' is-lit' : ''; ?>"></span>
			<?php endfor; ?>
		</div>
	</section>

	<?php if ( 'street' === $euteajudo_variant ) : ?>
		<div class="eta-street" aria-hidden="true">
			<?php echo \EuTeAjudo\Site\Street::svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG built from numbers. ?>
		</div>
	<?php endif; ?>

	<section class="eta-how">
		<h2><?php esc_html_e( 'How it works', 'euteajudo' ); ?></h2>
		<ol class="eta-steps">
			<li><strong><?php esc_html_e( 'Create an account with just an email.', 'euteajudo' ); ?></strong> <?php esc_html_e( 'Here you become a Friend with a number, like Friend 48213. Nobody knows who you are.', 'euteajudo' ); ?></li>
			<li><strong><?php esc_html_e( 'Write what is happening.', 'euteajudo' ); ?></strong> <?php esc_html_e( 'Your way, in your time. Nothing needs to be organised.', 'euteajudo' ); ?></li>
			<li><strong><?php esc_html_e( 'Get a reply written with care.', 'euteajudo' ); ?></strong> <?php esc_html_e( 'Replies are written by an artificial intelligence made to welcome people. We let you know by email, without showing the content.', 'euteajudo' ); ?></li>
		</ol>
		<?php $euteajudo_about = ContentPages::url( ContentPages::ABOUT ); ?>
		<?php if ( '' !== $euteajudo_about ) : ?>
			<p><a href="<?php echo esc_url( $euteajudo_about ); ?>"><?php esc_html_e( 'More about how it works', 'euteajudo' ); ?></a></p>
		<?php endif; ?>
	</section>

	<section class="eta-urgent">
		<h2><?php esc_html_e( 'Need help right now?', 'euteajudo' ); ?></h2>
		<p><?php esc_html_e( 'CVV answers for free, at any time, by phone at 188 or by chat at cvv.org.br.', 'euteajudo' ); ?></p>
		<p><a class="eta-button eta-button-quiet wp-element-button eta-button-inline" href="tel:188"><?php esc_html_e( 'Call 188', 'euteajudo' ); ?></a></p>
	</section>
</div>
