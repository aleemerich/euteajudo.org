<?php
/**
 * Sign in with Google (OpenID Connect, authorization code flow with PKCE).
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Google;

use EuTeAjudo\Accounts\Consent;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Redirects;
use EuTeAjudo\Accounts\Roles;

/**
 * Two REST routes: `start` sends the browser to Google, `callback` receives it back.
 *
 * Only the scopes `openid email` are requested, so Google never sends a name or a photo. The
 * account is identified by Google's `sub`, as Google recommends, and only the email is stored.
 */
final class GoogleAuth {

	public const REST_NAMESPACE = 'euteajudo/v1';
	public const START_ROUTE    = '/auth/google/start';
	public const CALLBACK_ROUTE = '/auth/google/callback';
	public const COOKIE         = 'eta_google_state';
	public const SUBJECT_META   = '_eta_google_sub';
	public const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

	private const AUTH_ENDPOINT    = 'https://accounts.google.com/o/oauth2/v2/auth';
	private const TRANSIENT_PREFIX = 'eta_google_';
	private const TTL              = 600;

	/**
	 * Hooks the REST routes.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Registers the start and callback routes. Both are public by design: they are the login.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::START_ROUTE,
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'start' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			self::REST_NAMESPACE,
			self::CALLBACK_ROUTE,
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'callback' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * OAuth client ID: environment variable locally, constant in wp-config.php in production.
	 */
	public static function client_id(): string {
		return self::setting( 'EUTEAJUDO_GOOGLE_CLIENT_ID' );
	}

	/**
	 * Redirect URI registered in the Google Cloud console.
	 */
	public static function callback_url(): string {
		return rest_url( self::REST_NAMESPACE . self::CALLBACK_ROUTE );
	}

	/**
	 * Sends the browser to Google.
	 *
	 * Parameters: `intent` (login or register); for register, `adult` and `consent` must be 1;
	 * optional `redirect_to` (same site only).
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function start( \WP_REST_Request $request ): \WP_REST_Response {
		$intent = 'register' === $request->get_param( 'intent' ) ? 'register' : 'login';
		if ( 'register' === $intent && ( '1' !== (string) $request->get_param( 'adult' ) || '1' !== (string) $request->get_param( 'consent' ) ) ) {
			return self::fail( 'consent_required' );
		}
		return self::redirect( self::begin( $intent, (string) $request->get_param( 'redirect_to' ) ) );
	}

	/**
	 * Prepares the authorization request and returns the Google URL to send the browser to, or
	 * an error URL when sign in with Google is not configured. Callers check age and consent
	 * before a registration.
	 *
	 * @param string $intent      "login" or "register".
	 * @param string $redirect_to Where to go after signing in (same site only).
	 */
	public static function begin( string $intent, string $redirect_to ): string {
		if ( '' === self::client_id() || '' === self::setting( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET' ) ) {
			return self::error_url( 'not_configured' );
		}

		$state    = Pkce::random_token();
		$nonce    = Pkce::random_token();
		$verifier = Pkce::random_token();
		set_transient(
			self::transient_key( $state ),
			array(
				'nonce'       => $nonce,
				'verifier'    => $verifier,
				'intent'      => 'register' === $intent ? 'register' : 'login',
				'redirect_to' => $redirect_to,
			),
			self::TTL
		);
		self::set_state_cookie( $state, time() + self::TTL );

		return add_query_arg(
			array(
				'client_id'             => rawurlencode( self::client_id() ),
				'redirect_uri'          => rawurlencode( self::callback_url() ),
				'response_type'         => 'code',
				'scope'                 => rawurlencode( 'openid email' ),
				'state'                 => $state,
				'nonce'                 => $nonce,
				'code_challenge'        => Pkce::challenge( $verifier ),
				'code_challenge_method' => 'S256',
				'prompt'                => 'select_account',
			),
			self::AUTH_ENDPOINT
		);
	}

	/**
	 * Receives the browser back from Google, checks everything and signs the person in.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function callback( \WP_REST_Request $request ): \WP_REST_Response {
		$state  = (string) $request->get_param( 'state' );
		$cookie = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
		self::set_state_cookie( '', time() - HOUR_IN_SECONDS );

		if ( null !== $request->get_param( 'error' ) ) {
			return self::fail( 'denied' );
		}
		if ( '' === $state || '' === $cookie || ! hash_equals( $cookie, $state ) ) {
			return self::fail( 'state' );
		}

		$pending = get_transient( self::transient_key( $state ) );
		delete_transient( self::transient_key( $state ) );
		if ( ! is_array( $pending ) ) {
			return self::fail( 'expired' );
		}

		$id_token = self::exchange_code( (string) $request->get_param( 'code' ), (string) $pending['verifier'] );
		if ( null === $id_token ) {
			return self::fail( 'token_exchange' );
		}

		try {
			$token = IdToken::validate( $id_token, self::client_id(), (string) $pending['nonce'], time() );
		} catch ( InvalidIdToken ) {
			return self::fail( 'id_token' );
		}

		$user_id = self::find_or_create_user( $token, 'register' === $pending['intent'] );
		if ( null === $user_id ) {
			return self::fail( 'needs_registration' );
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );

		return self::redirect( Redirects::after_sign_in( (string) $pending['redirect_to'] ) );
	}

	/**
	 * Finds the account for a Google identity: by subject, then by email (linking it), and
	 * creates one only for a registration, where age and consent were confirmed.
	 *
	 * @param IdToken $token    Validated token.
	 * @param bool    $register Whether the person came from the registration form.
	 */
	private static function find_or_create_user( IdToken $token, bool $register ): ?int {
		$by_subject = get_users(
			array(
				'fields'     => 'ID',
				'number'     => 1,
				'meta_key'   => self::SUBJECT_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Login only.
				'meta_value' => $token->subject, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Login only.
			)
		);
		if ( array() !== $by_subject ) {
			return (int) $by_subject[0];
		}

		$by_email = get_user_by( 'email', $token->email );
		if ( $by_email instanceof \WP_User ) {
			update_user_meta( $by_email->ID, self::SUBJECT_META, $token->subject );
			EmailConfirmation::mark_confirmed( $by_email->ID );
			return $by_email->ID;
		}

		if ( ! $register ) {
			return null;
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => 'u' . strtolower( wp_generate_password( 15, false ) ),
				'user_email' => $token->email,
				'user_pass'  => wp_generate_password( 32, true, true ),
				'role'       => Roles::FRIEND,
			)
		);
		if ( is_wp_error( $user_id ) ) {
			return null;
		}
		update_user_meta( $user_id, self::SUBJECT_META, $token->subject );
		Consent::record( $user_id, time() );
		EmailConfirmation::mark_confirmed( $user_id );
		return $user_id;
	}

	/**
	 * Exchanges the authorization code for an ID token.
	 *
	 * @param string $code     Authorization code.
	 * @param string $verifier PKCE code verifier.
	 */
	private static function exchange_code( string $code, string $verifier ): ?string {
		$response = wp_remote_post(
			self::TOKEN_ENDPOINT,
			array(
				'timeout' => 10,
				'body'    => array(
					'code'          => $code,
					'client_id'     => self::client_id(),
					'client_secret' => self::setting( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET' ),
					'redirect_uri'  => self::callback_url(),
					'grant_type'    => 'authorization_code',
					'code_verifier' => $verifier,
				),
			)
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) && is_string( $body['id_token'] ?? null ) ? $body['id_token'] : null;
	}

	/**
	 * Reads a setting from the environment, then from a wp-config.php constant.
	 *
	 * @param string $name Setting name.
	 */
	private static function setting( string $name ): string {
		$value = getenv( $name );
		if ( is_string( $value ) && '' !== $value ) {
			return $value;
		}
		return defined( $name ) ? (string) constant( $name ) : '';
	}

	/**
	 * Transient key for a state value.
	 *
	 * @param string $state State.
	 */
	private static function transient_key( string $state ): string {
		return self::TRANSIENT_PREFIX . hash( 'sha256', $state );
	}

	/**
	 * Sets or clears the cookie that binds the state to this browser.
	 *
	 * @param string $value   Cookie value.
	 * @param int    $expires Expiry time.
	 */
	private static function set_state_cookie( string $value, int $expires ): void {
		if ( headers_sent() ) {
			return;
		}
		setcookie(
			self::COOKIE,
			$value,
			array(
				'expires'  => $expires,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Redirect response.
	 *
	 * @param string $url Target URL.
	 */
	private static function redirect( string $url ): \WP_REST_Response {
		return new \WP_REST_Response( null, 302, array( 'Location' => $url ) );
	}

	/**
	 * Redirects back to the site with an error code the sign-in page can explain.
	 *
	 * @param string $code Error code.
	 */
	private static function fail( string $code ): \WP_REST_Response {
		return self::redirect( self::error_url( $code ) );
	}

	/**
	 * Page that explains an error: the sign-up page for registration errors, else sign-in.
	 *
	 * @param string $code Error code.
	 */
	private static function error_url( string $code ): string {
		$page = in_array( $code, array( 'consent_required', 'needs_registration' ), true ) ? Pages::SIGN_UP : Pages::SIGN_IN;
		return Pages::url( $page, array( 'eta_auth_error' => $code ) );
	}
}
