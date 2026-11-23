<?php
/**
 * Routes account form submissions and links.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\HelpRequests\RequestForms;

/**
 * Account forms post to their own page; this class checks the nonce, calls the handler and
 * redirects (post/redirect/get). It also handles confirmation links and page-level rules.
 */
final class Dispatcher {

	public const ACTION_FIELD = 'eta_action';
	public const NONCE_FIELD  = '_eta_nonce';

	/**
	 * Fields each form may submit. Anything else is ignored.
	 */
	private const FIELDS = array(
		'sign_in'          => array( 'method', 'email', 'password', 'redirect_to' ),
		'sign_up'          => array( 'age', 'consent', 'method', 'email', 'password' ),
		'password_request' => array( 'email' ),
		'password_reset'   => array( 'login', 'key', 'password' ),
		'delete_account'   => array( 'confirm' ),
		'write'            => array( 'title', 'body' ),
		'reply'            => array( 'code', 'text' ),
		'thanks'           => array( 'code', 'reply' ),
		'close'            => array( 'code', 'reason' ),
		'reopen'           => array( 'code' ),
	);

	/**
	 * Hooks the dispatcher.
	 */
	public static function register(): void {
		add_action( 'template_redirect', array( self::class, 'handle_request' ) );
		add_filter( 'wp_robots', array( self::class, 'robots' ) );
		// Core emails the administrator on every password reset; not useful here.
		remove_action( 'after_password_reset', 'wp_password_change_notification' );
	}

	/**
	 * Nonce action for a form.
	 *
	 * @param string $form Form key.
	 */
	public static function nonce_action( string $form ): string {
		return 'eta_' . $form;
	}

	/**
	 * Handles posts and links on account pages.
	 */
	public static function handle_request(): void {
		if ( ! Pages::is_current() ) {
			return;
		}
		nocache_headers();

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && ( is_user_logged_in() || ! Pages::is_current( array( Pages::ACCOUNT, Pages::WRITE, Pages::MY_REQUESTS ) ) ) ) {
			self::finish( self::dispatch_post() );
		}

		if ( Pages::is_current( array( Pages::EMAIL_CONFIRMATION ) ) && isset( $_GET['u'], $_GET['t'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The token is the proof.
			$user_id = absint( $_GET['u'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Same.
			$token   = sanitize_text_field( wp_unslash( $_GET['t'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Same.
			if ( EmailConfirmation::confirm( $user_id, $token ) ) {
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id, true, is_ssl() );
				self::finish( Pages::url( Pages::EMAIL_CONFIRMATION, array( 'eta_step' => 'email_confirmed' ) ) );
			}
			self::finish( Pages::url( Pages::EMAIL_CONFIRMATION, array( 'eta_auth_error' => 'confirmation_invalid' ) ) );
		}

		if ( is_user_logged_in() && Pages::is_current( array( Pages::SIGN_IN, Pages::SIGN_UP ) ) ) {
			self::finish( home_url( '/' ) );
		}

		if ( ! is_user_logged_in() && Pages::is_current( array( Pages::ACCOUNT, Pages::WRITE, Pages::MY_REQUESTS ) ) ) {
			$here = isset( $_SERVER['REQUEST_URI'] ) ? home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : home_url( '/' );
			self::finish( Pages::url( Pages::SIGN_IN, array( 'redirect_to' => $here ) ) );
		}
	}

	/**
	 * Validates a form post and runs its handler.
	 */
	private static function dispatch_post(): string {
		$form = isset( $_POST[ self::ACTION_FIELD ] ) ? sanitize_key( wp_unslash( $_POST[ self::ACTION_FIELD ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified right below.
		if ( ! isset( self::FIELDS[ $form ] ) ) {
			return home_url( '/' );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::nonce_action( $form ) ) ) {
			return add_query_arg( 'eta_auth_error', 'expired_form', (string) wp_get_referer() );
		}

		$input = array();
		foreach ( self::FIELDS[ $form ] as $field ) {
			// Passwords are taken verbatim; handlers sanitize every other field for its use.
			$input[ $field ] = isset( $_POST[ $field ] ) && is_string( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- See comment above.
		}

		return match ( $form ) {
			'sign_in'          => SignIn::handle( $input ),
			'sign_up'          => SignUp::handle( $input ),
			'password_request' => PasswordReset::request( $input ),
			'password_reset'   => PasswordReset::reset( $input ),
			'delete_account'   => AccountForm::delete( $input ),
			'write'            => RequestForms::write( $input ),
			'reply'            => RequestForms::reply( $input ),
			'thanks'           => RequestForms::thanks( $input ),
			'close'            => RequestForms::close( $input ),
			'reopen'           => RequestForms::reopen( $input ),
		};
	}

	/**
	 * Redirects and stops. Google URLs are allowed explicitly; everything else must be local.
	 *
	 * @param string $url Target.
	 */
	private static function finish( string $url ): never {
		if ( str_starts_with( $url, 'https://accounts.google.com/' ) ) {
			wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Fixed Google endpoint built by GoogleAuth.
		} else {
			wp_safe_redirect( $url );
		}
		exit;
	}

	/**
	 * Keeps account pages out of search engines.
	 *
	 * @param array<string, bool|string> $robots Robots directives.
	 * @return array<string, bool|string>
	 */
	public static function robots( array $robots ): array {
		if ( Pages::is_current() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
}
