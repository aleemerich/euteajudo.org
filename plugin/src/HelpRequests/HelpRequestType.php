<?php
/**
 * Help request post type, statuses and triage taxonomies.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

/**
 * Registers the native structures of ADR 0003: a non-public custom post type, non-public custom
 * statuses and non-public taxonomies. Nothing here is reachable by URL, search, feed or sitemap;
 * the plugin shows requests only to their author and to administrators (and, from E3, to Agents
 * through the REST API).
 */
final class HelpRequestType {

	public const POST_TYPE = 'eta_help_request';
	public const REPLY     = 'eta_reply';

	public const AWAITING_TRIAGE = 'eta_awaiting_triage';
	public const AWAITING_REPLY  = 'eta_awaiting_reply';
	public const IN_CONVERSATION = 'eta_in_conversation';
	public const CLOSED          = 'eta_closed';
	public const STATUSES        = array( self::AWAITING_TRIAGE, self::AWAITING_REPLY, self::IN_CONVERSATION, self::CLOSED );

	public const CATEGORY    = 'eta_category';
	public const SEVERITY    = 'eta_severity';
	public const RISK_SIGNAL = 'eta_risk_signal';
	public const TAXONOMIES  = array( self::CATEGORY, self::SEVERITY, self::RISK_SIGNAL );

	/**
	 * Capabilities administrators get over help requests.
	 */
	public const ADMIN_CAPS = array(
		'edit_eta_help_requests',
		'edit_others_eta_help_requests',
		'read_private_eta_help_requests',
		'delete_eta_help_requests',
		'delete_others_eta_help_requests',
		'delete_private_eta_help_requests',
		'edit_private_eta_help_requests',
	);

	/**
	 * Hooks registration early on init, before migrations (init 5) need the taxonomies.
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_all' ), 0 );
	}

	/**
	 * Registers post type, statuses and taxonomies.
	 */
	public static function register_all(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Help requests', 'euteajudo' ),
					'singular_name'      => __( 'Help request', 'euteajudo' ),
					'edit_item'          => __( 'Help request', 'euteajudo' ),
					'all_items'          => __( 'All help requests', 'euteajudo' ),
					'search_items'       => __( 'Search help requests', 'euteajudo' ),
					'not_found'          => __( 'No help requests found.', 'euteajudo' ),
					'not_found_in_trash' => __( 'No help requests found in Trash.', 'euteajudo' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_icon'           => 'dashicons-format-chat',
				'supports'            => array( 'title', 'editor', 'excerpt', 'comments', 'author' ),
				'capability_type'     => array( 'eta_help_request', 'eta_help_requests' ),
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ), // Only Friends write requests, on the site.
				'map_meta_cap'        => true,
			)
		);

		$labels = array(
			self::AWAITING_TRIAGE => array(
				__( 'Awaiting triage', 'euteajudo' ),
				/* translators: %s: number of help requests. */
				_n_noop( 'Awaiting triage <span class="count">(%s)</span>', 'Awaiting triage <span class="count">(%s)</span>', 'euteajudo' ),
			),
			self::AWAITING_REPLY  => array(
				__( 'Awaiting reply', 'euteajudo' ),
				/* translators: %s: number of help requests. */
				_n_noop( 'Awaiting reply <span class="count">(%s)</span>', 'Awaiting reply <span class="count">(%s)</span>', 'euteajudo' ),
			),
			self::IN_CONVERSATION => array(
				__( 'In conversation', 'euteajudo' ),
				/* translators: %s: number of help requests. */
				_n_noop( 'In conversation <span class="count">(%s)</span>', 'In conversation <span class="count">(%s)</span>', 'euteajudo' ),
			),
			self::CLOSED          => array(
				__( 'Closed', 'euteajudo' ),
				/* translators: %s: number of help requests. */
				_n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'euteajudo' ),
			),
		);
		foreach ( $labels as $status => [ $label, $count ] ) {
			register_post_status(
				$status,
				array(
					'label'                     => $label,
					'label_count'               => $count,
					'public'                    => false,
					'private'                   => true,
					'internal'                  => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
				)
			);
		}

		$taxonomies = array(
			self::CATEGORY    => __( 'Categories', 'euteajudo' ),
			self::SEVERITY    => __( 'Severity', 'euteajudo' ),
			self::RISK_SIGNAL => __( 'Risk signals', 'euteajudo' ),
		);
		foreach ( $taxonomies as $taxonomy => $label ) {
			register_taxonomy(
				$taxonomy,
				array( self::POST_TYPE, \EuTeAjudo\SupportServices\SupportServiceType::POST_TYPE ),
				array(
					'label'              => $label,
					'public'             => false,
					'publicly_queryable' => false,
					'show_ui'            => true,
					'show_in_rest'       => false,
					'show_admin_column'  => true,
					'hierarchical'       => false,
					'rewrite'            => false,
					'query_var'          => false,
					'capabilities'       => array(
						'manage_terms' => 'manage_categories',
						'edit_terms'   => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_eta_help_requests',
					),
				)
			);
		}
	}
}
