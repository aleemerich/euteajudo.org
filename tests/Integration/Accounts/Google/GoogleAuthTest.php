<?php
/**
 * Integration tests: sign in with Google.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts\Google;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\Consent;
use EuTeAjudo\Accounts\Google\GoogleAuth;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Tests\Integration\TestCase;
use EuTeAjudo\Tests\Support\FakeGoogle;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Authorization request, callback checks and account creation.
 */
final class GoogleAuthTest extends TestCase {

	/**
	 * Claims the fake token endpoint returns (the nonce is filled from the authorization request).
	 *
	 * @var array<string, mixed>
	 */
	private array $claims = array();

	/**
	 * Nonce sent to Google in the authorization request.
	 *
	 * @var string
	 */
	private string $nonce = '';

	public function set_up(): void {
		parent::set_up();
		\EuTeAjudo\Accounts\Pages::install();
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_ID=' . FakeGoogle::CLIENT_ID );
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET=secret-xyz' );
		add_filter( 'send_auth_cookies', '__return_false' );
		add_filter( 'pre_http_request', array( $this, 'fake_token_endpoint' ), 10, 3 );
		$_COOKIE = array();
	}

	public function tear_down(): void {
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_ID' );
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET' );
		$_COOKIE = array();
		parent::tear_down();
	}

	/**
	 * Answers requests to Google's token endpoint.
	 *
	 * @param mixed                $pre  Preempted response.
	 * @param array<string, mixed> $args Request arguments.
	 * @param string               $url  Request URL.
	 * @return mixed
	 */
	public function fake_token_endpoint( mixed $pre, array $args, string $url ): mixed {
		if ( GoogleAuth::TOKEN_ENDPOINT !== $url ) {
			return $pre;
		}
		$body = is_array( $args['body'] ?? null ) ? $args['body'] : array();
		if ( 'secret-xyz' !== ( $body['client_secret'] ?? null ) || ! isset( $body['code_verifier'] ) ) {
			return array(
				'response' => array(
					'code'    => 400,
					'message' => 'Bad Request',
				),
				'body'     => '{}',
				'headers'  => array(),
				'cookies'  => array(),
			);
		}
		$jwt = FakeGoogle::jwt( array_merge( array( 'nonce' => $this->nonce ), $this->claims ), time() );
		return array(
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'body'     => (string) wp_json_encode( array( 'id_token' => $jwt ) ),
			'headers'  => array(),
			'cookies'  => array(),
		);
	}

	/**
	 * Calls a REST route with GET parameters.
	 *
	 * @param string               $route  Route under the plugin namespace.
	 * @param array<string, mixed> $params Query parameters.
	 */
	private function get( string $route, array $params ): WP_REST_Response {
		$request = new WP_REST_Request( 'GET', '/' . GoogleAuth::REST_NAMESPACE . $route );
		$request->set_query_params( $params );
		$response = rest_do_request( $request );
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		return $response;
	}

	/**
	 * Location header of a redirect response.
	 *
	 * @param WP_REST_Response $response Response.
	 */
	private function location( WP_REST_Response $response ): string {
		$this->assertSame( 302, $response->get_status() );
		return (string) ( $response->get_headers()['Location'] ?? '' );
	}

	/**
	 * Starts the flow and returns the state sent to Google, simulating the browser cookie.
	 *
	 * @param array<string, mixed> $params Start parameters.
	 * @return array<string, string> Query parameters of the Google authorization URL.
	 */
	private function start( array $params ): array {
		$url = $this->location( $this->get( GoogleAuth::START_ROUTE, $params ) );
		$this->assertStringStartsWith( 'https://accounts.google.com/o/oauth2/v2/auth?', $url );
		$query                         = $this->query_of( $url );
		$this->nonce                   = $query['nonce'] ?? '';
		$_COOKIE[ GoogleAuth::COOKIE ] = $query['state'] ?? '';
		return $query;
	}

	/**
	 * String query parameters of a URL.
	 *
	 * @param string $url URL.
	 * @return array<string, string>
	 */
	private function query_of( string $url ): array {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$strings = array();
		foreach ( $query as $key => $value ) {
			if ( is_string( $value ) ) {
				$strings[ (string) $key ] = $value;
			}
		}
		return $strings;
	}

	/**
	 * Calls the callback for a given state.
	 *
	 * @param string $state State returned by Google.
	 */
	private function return_from_google( string $state ): string {
		return $this->location(
			$this->get(
				GoogleAuth::CALLBACK_ROUTE,
				array(
					'code'  => 'auth-code',
					'state' => $state,
				)
			)
		);
	}

	/**
	 * Error code carried by a redirect URL, if any.
	 *
	 * @param string $url Redirect URL.
	 */
	private function error_in( string $url ): string {
		return $this->query_of( $url )['eta_auth_error'] ?? '';
	}

	public function test_authorization_request_asks_only_for_openid_and_email_with_pkce(): void {
		$query = $this->start(
			array(
				'intent'  => 'register',
				'adult'   => '1',
				'consent' => '1',
			)
		);

		$this->assertSame( FakeGoogle::CLIENT_ID, $query['client_id'] );
		$this->assertSame( 'openid email', $query['scope'] );
		$this->assertSame( 'code', $query['response_type'] );
		$this->assertSame( 'S256', $query['code_challenge_method'] );
		$this->assertNotSame( '', $query['code_challenge'] );
		$this->assertSame( rest_url( GoogleAuth::REST_NAMESPACE . GoogleAuth::CALLBACK_ROUTE ), $query['redirect_uri'] );
	}

	public function test_registration_creates_an_anonymous_friend_and_signs_in(): void {
		$query = $this->start(
			array(
				'intent'  => 'register',
				'adult'   => '1',
				'consent' => '1',
			)
		);

		$url = $this->return_from_google( $query['state'] );

		$this->assertSame( \EuTeAjudo\Accounts\Pages::url( \EuTeAjudo\Accounts\Pages::WRITE ), $url, 'A new account goes straight to Write.' );
		$user = get_user_by( 'email', 'someone@example.org' );
		$this->assertInstanceOf( \WP_User::class, $user );
		$this->assertSame( array( Roles::FRIEND ), $user->roles );
		$this->assertSame( Aliases::label( Aliases::number_of( $user->ID ) ), $user->display_name );
		$this->assertSame( 'friend-' . Aliases::number_of( $user->ID ), $user->user_login );
		$this->assertSame( '', $user->first_name );
		$this->assertSame( '110169484474386276334', get_user_meta( $user->ID, GoogleAuth::SUBJECT_META, true ) );
		$this->assertSame( Consent::VERSION, Consent::version_of( $user->ID ) );
		$this->assertSame( $user->ID, get_current_user_id() );
	}

	public function test_registration_requires_age_declaration_and_consent(): void {
		$url = $this->location(
			$this->get(
				GoogleAuth::START_ROUTE,
				array(
					'intent' => 'register',
					'adult'  => '1',
				)
			)
		);

		$this->assertSame( 'consent_required', $this->error_in( $url ) );
	}

	public function test_refuses_a_state_that_does_not_match_the_browser_cookie(): void {
		$query                         = $this->start(
			array(
				'intent'  => 'register',
				'adult'   => '1',
				'consent' => '1',
			)
		);
		$_COOKIE[ GoogleAuth::COOKIE ] = 'another-browser';

		$this->assertSame( 'state', $this->error_in( $this->return_from_google( $query['state'] ) ) );
		$this->assertFalse( get_user_by( 'email', 'someone@example.org' ) );
	}

	public function test_a_state_can_be_used_only_once(): void {
		$query = $this->start(
			array(
				'intent'  => 'register',
				'adult'   => '1',
				'consent' => '1',
			)
		);
		$this->return_from_google( $query['state'] );
		wp_set_current_user( 0 );

		$this->assertSame( 'expired', $this->error_in( $this->return_from_google( $query['state'] ) ) );
	}

	public function test_refuses_unverified_google_emails(): void {
		$this->claims = array( 'email_verified' => false );
		$query        = $this->start(
			array(
				'intent'  => 'register',
				'adult'   => '1',
				'consent' => '1',
			)
		);

		$this->assertSame( 'id_token', $this->error_in( $this->return_from_google( $query['state'] ) ) );
		$this->assertFalse( get_user_by( 'email', 'someone@example.org' ) );
	}

	public function test_existing_account_with_the_same_email_is_linked_not_duplicated(): void {
		$existing = $this->new_user( array( 'user_email' => 'someone@example.org' ) );
		$query    = $this->start( array( 'intent' => 'login' ) );

		$this->assertSame( '', $this->error_in( $this->return_from_google( $query['state'] ) ) );
		$this->assertSame( $existing->ID, get_current_user_id() );
		$this->assertSame( '110169484474386276334', get_user_meta( $existing->ID, GoogleAuth::SUBJECT_META, true ) );
	}

	public function test_login_with_an_unknown_google_account_asks_to_register_first(): void {
		$query = $this->start( array( 'intent' => 'login' ) );

		$this->assertSame( 'needs_registration', $this->error_in( $this->return_from_google( $query['state'] ) ) );
		$this->assertFalse( get_user_by( 'email', 'someone@example.org' ) );
	}

	public function test_without_credentials_the_flow_does_not_start(): void {
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_ID' );

		$url = $this->location( $this->get( GoogleAuth::START_ROUTE, array( 'intent' => 'login' ) ) );

		$this->assertSame( 'not_configured', $this->error_in( $url ) );
	}
}
