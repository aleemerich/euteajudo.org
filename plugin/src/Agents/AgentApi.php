<?php
/**
 * REST API for Agents.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Agents;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\View;
use EuTeAjudo\SupportServices\SupportServices;

/**
 * Routes under /wp-json/euteajudo/v1 (ADR 0007). Only users with the Agent capability reach
 * them, authenticated with an Application Password over HTTPS. Responses never carry the
 * author's user ID, email or login.
 */
final class AgentApi {

	public const NAMESPACE = 'euteajudo/v1';

	/**
	 * Hooks route registration.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	/**
	 * Registers the routes.
	 */
	public static function routes(): void {
		$id     = array(
			'id' => array(
				'type'     => 'integer',
				'required' => true,
			),
		);
		$routes = array(
			'/queue'                            => array(
				'methods'  => 'GET',
				'callback' => array( self::class, 'queue' ),
				'args'     => array(
					'limit' => array(
						'type'    => 'integer',
						'default' => 10,
						'minimum' => 1,
						'maximum' => 50,
					),
				),
			),
			'/vocabulary'                       => array(
				'methods'  => 'GET',
				'callback' => array( self::class, 'vocabulary' ),
			),
			'/requests/(?P<id>\d+)'             => array(
				'methods'  => 'GET',
				'callback' => array( self::class, 'detail' ),
				'args'     => $id,
			),
			'/requests/(?P<id>\d+)/reservation' => array(
				array(
					'methods'  => 'POST',
					'callback' => array( self::class, 'reserve' ),
					'args'     => $id,
				),
				array(
					'methods'  => 'DELETE',
					'callback' => array( self::class, 'release' ),
					'args'     => $id,
				),
			),
			'/requests/(?P<id>\d+)/triage'      => array(
				'methods'  => 'POST',
				'callback' => array( self::class, 'triage' ),
				'args'     => $id + array(
					'categories'   => array(
						'type'     => 'array',
						'items'    => array( 'type' => 'string' ),
						'required' => true,
					),
					'severity'     => array(
						'type'     => 'string',
						'required' => true,
					),
					'risk_signals' => array(
						'type'    => 'array',
						'items'   => array( 'type' => 'string' ),
						'default' => array(),
					),
					'summary'      => array(
						'type'     => 'string',
						'required' => true,
					),
				),
			),
			'/requests/(?P<id>\d+)/replies'     => array(
				'methods'  => 'POST',
				'callback' => array( self::class, 'reply' ),
				'args'     => $id + array(
					'text'        => array(
						'type'     => 'string',
						'required' => true,
					),
					'in_reply_to' => array(
						'type'     => 'integer',
						'required' => true,
						'minimum'  => 0,
					),
				),
			),
			'/requests/(?P<id>\d+)/failures'    => array(
				'methods'  => 'POST',
				'callback' => array( self::class, 'fail' ),
				'args'     => $id,
			),
		);
		foreach ( $routes as $path => $endpoints ) {
			$endpoints = isset( $endpoints['methods'] ) ? array( $endpoints ) : $endpoints;
			foreach ( $endpoints as &$endpoint ) {
				$endpoint['permission_callback'] = array( self::class, 'can_work' );
			}
			unset( $endpoint );
			register_rest_route( self::NAMESPACE, $path, $endpoints );
		}
	}

	/**
	 * Only Agents; records when they were last seen.
	 */
	public static function can_work(): bool|\WP_Error {
		if ( ! current_user_can( AgentWork::CAP ) ) {
			return new \WP_Error( 'rest_forbidden', 'Agents only.', array( 'status' => rest_authorization_required_code() ) );
		}
		AgentWork::touch( get_current_user_id() );
		return true;
	}

	/**
	 * GET /queue.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function queue( \WP_REST_Request $request ): \WP_REST_Response {
		$items = array_map(
			static fn ( \WP_Post $post ): array => array(
				'id'       => $post->ID,
				'status'   => $post->post_status,
				'failures' => AgentWork::failures( $post->ID ),
			),
			AgentWork::pending( get_current_user_id(), (int) $request['limit'] )
		);
		return new \WP_REST_Response( array( 'items' => $items ) );
	}

	/**
	 * GET /vocabulary: term slugs the triage accepts.
	 */
	public static function vocabulary(): \WP_REST_Response {
		$out = array();
		foreach ( array(
			'categories'   => Type::CATEGORY,
			'severities'   => Type::SEVERITY,
			'risk_signals' => Type::RISK_SIGNAL,
		) as $key => $taxonomy ) {
			$terms       = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'fields'     => 'id=>slug',
				)
			);
			$out[ $key ] = is_array( $terms ) ? array_values( array_map( 'strval', $terms ) ) : array();
		}
		return new \WP_REST_Response( $out );
	}

	/**
	 * GET /requests/{id}: everything an Agent needs to triage and reply.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function detail( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post = AgentWork::open_request( (int) $request['id'] );
		if ( null === $post ) {
			return new \WP_Error( 'eta_not_found', 'Request not found.', array( 'status' => 404 ) );
		}
		$author   = (int) $post->post_author;
		$messages = array(
			array(
				'id'   => 0,
				'from' => 'author',
				'text' => $post->post_content,
				'date' => mysql_to_rfc3339( $post->post_date_gmt ),
			),
		);
		foreach ( View::replies( $post->ID ) as $reply ) {
			$from       = (int) $reply->user_id === $author ? 'author' : 'helper';
			$messages[] = array(
				'id'      => (int) $reply->comment_ID,
				'from'    => $from,
				'alias'   => $reply->comment_author,
				'text'    => $reply->comment_content,
				'date'    => mysql_to_rfc3339( $reply->comment_date_gmt ),
				'thanked' => 'helper' === $from && Replies::is_thanked( (int) $reply->comment_ID ),
			);
		}
		$triage = array(
			'main_category' => (string) get_post_meta( $post->ID, AgentWork::MAIN_CATEGORY_META, true ),
			'categories'    => self::slugs( $post->ID, Type::CATEGORY ),
			'severity'      => self::slugs( $post->ID, Type::SEVERITY )[0] ?? '',
			'risk_signals'  => self::slugs( $post->ID, Type::RISK_SIGNAL ),
			'summary'       => $post->post_excerpt,
		);
		return new \WP_REST_Response(
			array(
				'id'                     => $post->ID,
				'status'                 => $post->post_status,
				'title'                  => $post->post_title,
				'author_alias'           => Aliases::label( Aliases::number_of( $author ) ),
				'agent_alias'            => AgentAliases::label_for_request( $post->ID ),
				'created_at'             => mysql_to_rfc3339( $post->post_date_gmt ),
				'author_active_at'       => gmdate( 'c', (int) get_post_meta( $post->ID, HelpRequests::AUTHOR_ACTIVE_META, true ) ),
				'last_author_message_id' => AgentWork::last_author_message_id( $post ),
				'failures'               => AgentWork::failures( $post->ID ),
				'reserved_by_you'        => AgentWork::holds( $post->ID, get_current_user_id() ),
				'triage'                 => $triage,
				'messages'               => $messages,
				'support_services'       => SupportServices::candidates( $triage['categories'], $triage['risk_signals'] ),
			)
		);
	}

	/**
	 * POST /requests/{id}/reservation.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function reserve( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$until = AgentWork::reserve( (int) $request['id'], get_current_user_id() );
		return is_wp_error( $until ) ? $until : new \WP_REST_Response( array( 'reserved_until' => gmdate( 'c', $until ) ) );
	}

	/**
	 * DELETE /requests/{id}/reservation.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function release( \WP_REST_Request $request ): \WP_REST_Response {
		AgentWork::release( (int) $request['id'], get_current_user_id() );
		return new \WP_REST_Response( array( 'released' => true ) );
	}

	/**
	 * POST /requests/{id}/triage.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function triage( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$result = AgentWork::triage(
			(int) $request['id'],
			get_current_user_id(),
			self::strings( $request['categories'] ),
			(string) $request['severity'],
			self::strings( $request['risk_signals'] ),
			(string) $request['summary']
		);
		return is_wp_error( $result ) ? $result : new \WP_REST_Response( array( 'status' => Type::AWAITING_REPLY ) );
	}

	/**
	 * POST /requests/{id}/replies.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function reply( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$id = AgentWork::reply( (int) $request['id'], get_current_user_id(), (string) $request['text'], (int) $request['in_reply_to'] );
		return is_wp_error( $id ) ? $id : new \WP_REST_Response( array( 'id' => $id ), 201 );
	}

	/**
	 * POST /requests/{id}/failures.
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function fail( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$result = AgentWork::fail( (int) $request['id'], get_current_user_id() );
		return is_wp_error( $result ) ? $result : new \WP_REST_Response( $result );
	}

	/**
	 * Term slugs of a request.
	 *
	 * @param int    $post_id  Request ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string[]
	 */
	private static function slugs( int $post_id, string $taxonomy ): array {
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
		return is_array( $terms ) ? array_values( array_map( 'strval', $terms ) ) : array();
	}

	/**
	 * Array of strings from a request value.
	 *
	 * @param mixed $value Value.
	 * @return string[]
	 */
	private static function strings( mixed $value ): array {
		return is_array( $value ) ? array_values( array_map( 'strval', array_filter( $value, 'is_scalar' ) ) ) : array();
	}
}
