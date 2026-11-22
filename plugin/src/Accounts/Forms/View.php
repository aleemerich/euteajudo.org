<?php
/**
 * Markup helpers for account forms.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

/**
 * Small, escaped building blocks shared by the account block templates.
 */
final class View {

	private const GOOGLE_G = '<svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>';

	/**
	 * Opening form tag with the action and nonce fields.
	 *
	 * @param string $form Form key.
	 */
	public static function open( string $form ): string {
		return '<form class="eta-form" method="post" novalidate>'
			. '<input type="hidden" name="' . esc_attr( Dispatcher::ACTION_FIELD ) . '" value="' . esc_attr( $form ) . '">'
			. wp_nonce_field( Dispatcher::nonce_action( $form ), Dispatcher::NONCE_FIELD, false, false );
	}

	/**
	 * Error notice for the current URL, if any.
	 */
	public static function error_notice(): string {
		$message = Messages::error( Messages::current_error() );
		return '' === $message ? '' : '<div class="eta-notice eta-notice-error" role="alert">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Neutral notice.
	 *
	 * @param string $message Message.
	 */
	public static function notice( string $message ): string {
		return '<div class="eta-notice" role="status">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Labelled email field.
	 *
	 * @param string $id Field ID.
	 */
	public static function email( string $id ): string {
		return '<label for="' . esc_attr( $id ) . '">' . esc_html__( 'Email', 'euteajudo' ) . '</label>'
			. '<input id="' . esc_attr( $id ) . '" name="email" type="email" autocomplete="email" inputmode="email">';
	}

	/**
	 * Labelled password field with a show/hide button.
	 *
	 * @param string $id           Field ID.
	 * @param string $label        Label.
	 * @param string $autocomplete Autocomplete token.
	 * @param string $hint         Optional hint below the field.
	 */
	public static function password( string $id, string $label, string $autocomplete, string $hint = '' ): string {
		$hint_id = $id . '-hint';
		return '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>'
			. '<div class="eta-password"><input id="' . esc_attr( $id ) . '" name="password" type="password" autocomplete="' . esc_attr( $autocomplete ) . '"' . ( '' !== $hint ? ' aria-describedby="' . esc_attr( $hint_id ) . '"' : '' ) . '>'
			. '<button type="button" class="eta-password-toggle" data-show="' . esc_attr__( 'Show', 'euteajudo' ) . '" data-hide="' . esc_attr__( 'Hide', 'euteajudo' ) . '" aria-controls="' . esc_attr( $id ) . '" hidden>' . esc_html__( 'Show', 'euteajudo' ) . '</button></div>'
			. ( '' !== $hint ? '<p class="eta-hint" id="' . esc_attr( $hint_id ) . '">' . esc_html( $hint ) . '</p>' : '' );
	}

	/**
	 * Invisible first submit button, so pressing Enter in a field submits with email and
	 * password instead of activating the Google button, which comes first visually.
	 */
	public static function default_submit(): string {
		return '<button class="eta-default-submit" type="submit" name="method" value="email" tabindex="-1" aria-hidden="true"></button>';
	}

	/**
	 * "Continue with Google" submit button, in Google's own button style.
	 */
	public static function google_button(): string {
		return '<button class="eta-google" type="submit" name="method" value="google" formnovalidate>' . self::GOOGLE_G . esc_html__( 'Continue with Google', 'euteajudo' ) . '</button>';
	}

	/**
	 * Divider between Google and email.
	 */
	public static function divider(): string {
		return '<div class="eta-divider"><span>' . esc_html__( 'or with email', 'euteajudo' ) . '</span></div>';
	}

	/**
	 * Primary submit button.
	 *
	 * @param string $label Label.
	 * @param string $value Value of the "method" field, if any.
	 */
	public static function submit( string $label, string $value = '' ): string {
		$name = '' === $value ? '' : ' name="method" value="' . esc_attr( $value ) . '"';
		return '<button class="eta-button wp-element-button" type="submit"' . $name . '>' . esc_html( $label ) . '</button>';
	}

	/**
	 * Link list under a form.
	 *
	 * @param array<string, string> $links URL => label.
	 */
	public static function links( array $links ): string {
		$items = '';
		foreach ( $links as $url => $label ) {
			$items .= '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		}
		return '<nav class="eta-links">' . $items . '</nav>';
	}
}
