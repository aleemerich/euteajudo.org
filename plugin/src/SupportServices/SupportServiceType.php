<?php
/**
 * Support service post type.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\SupportServices;

use EuTeAjudo\HelpRequests\HelpRequestType;

/**
 * Support services (CVV, SAMU...) the Agent may refer a Friend to. Managed by administrators in
 * wp-admin with the native screens; never public. Fields live in post meta; the same triage
 * taxonomies as help requests tell which cases each service fits.
 */
final class SupportServiceType {

	public const POST_TYPE = 'eta_support_service';

	public const PHONE_META = '_eta_phone';
	public const URL_META   = '_eta_url';
	public const HOURS_META = '_eta_hours';
	public const SCOPE_META = '_eta_scope';
	public const SCOPES     = array( 'national', 'digital' );

	public const ADMIN_CAPS = array(
		'edit_eta_support_services',
		'edit_others_eta_support_services',
		'publish_eta_support_services',
		'read_private_eta_support_services',
		'delete_eta_support_services',
		'delete_others_eta_support_services',
		'delete_published_eta_support_services',
		'delete_private_eta_support_services',
		'edit_published_eta_support_services',
		'edit_private_eta_support_services',
	);

	private const NONCE = 'eta_support_service_fields';

	/**
	 * Hooks registration and the fields box.
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_type' ), 0 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( self::class, 'add_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( self::class, 'save' ), 10, 2 );
	}

	/**
	 * Registers the post type and attaches the triage taxonomies.
	 */
	public static function register_type(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Support services', 'euteajudo' ),
					'singular_name' => __( 'Support service', 'euteajudo' ),
					'add_new_item'  => __( 'Add support service', 'euteajudo' ),
					'edit_item'     => __( 'Edit support service', 'euteajudo' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=' . HelpRequestType::POST_TYPE,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'editor' ),
				'capability_type'     => array( 'eta_support_service', 'eta_support_services' ),
				'map_meta_cap'        => true,
			)
		);
		foreach ( HelpRequestType::TAXONOMIES as $taxonomy ) {
			register_taxonomy_for_object_type( $taxonomy, self::POST_TYPE );
		}
	}

	/**
	 * Adds the fields box.
	 */
	public static function add_box(): void {
		add_meta_box( 'eta-support-service-fields', __( 'Contact', 'euteajudo' ), array( self::class, 'render_box' ), self::POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Renders the fields box.
	 *
	 * @param \WP_Post $post Service.
	 */
	public static function render_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		$scope = (string) get_post_meta( $post->ID, self::SCOPE_META, true );
		?>
		<p><label for="eta-phone"><?php esc_html_e( 'Phone', 'euteajudo' ); ?></label><br>
		<input class="regular-text" id="eta-phone" name="eta_phone" type="text" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, self::PHONE_META, true ) ); ?>"></p>
		<p><label for="eta-url"><?php esc_html_e( 'Website', 'euteajudo' ); ?></label><br>
		<input class="regular-text" id="eta-url" name="eta_url" type="url" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, self::URL_META, true ) ); ?>"></p>
		<p><label for="eta-hours"><?php esc_html_e( 'Opening hours', 'euteajudo' ); ?></label><br>
		<input class="regular-text" id="eta-hours" name="eta_hours" type="text" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, self::HOURS_META, true ) ); ?>"></p>
		<p><label for="eta-scope"><?php esc_html_e( 'Reach', 'euteajudo' ); ?></label><br>
		<select id="eta-scope" name="eta_scope">
			<option value="national" <?php selected( $scope, 'national' ); ?>><?php esc_html_e( 'National', 'euteajudo' ); ?></option>
			<option value="digital" <?php selected( $scope, 'digital' ); ?>><?php esc_html_e( 'Digital', 'euteajudo' ); ?></option>
		</select></p>
		<p class="description"><?php esc_html_e( 'Mark the categories, severities and risk signals this service fits. The Agent receives the services that match the triage of each request.', 'euteajudo' ); ?></p>
		<?php
	}

	/**
	 * Saves the fields.
	 *
	 * @param int      $post_id Service ID.
	 * @param \WP_Post $post    Service.
	 */
	public static function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$scope = sanitize_key( wp_unslash( $_POST['eta_scope'] ?? '' ) );
		self::save_fields(
			$post_id,
			sanitize_text_field( wp_unslash( $_POST['eta_phone'] ?? '' ) ),
			esc_url_raw( wp_unslash( $_POST['eta_url'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['eta_hours'] ?? '' ) ),
			in_array( $scope, self::SCOPES, true ) ? $scope : 'national'
		);
	}

	/**
	 * Stores the contact fields.
	 *
	 * @param int    $post_id Service ID.
	 * @param string $phone   Phone.
	 * @param string $url     Website.
	 * @param string $hours   Opening hours.
	 * @param string $scope   national or digital.
	 */
	public static function save_fields( int $post_id, string $phone, string $url, string $hours, string $scope ): void {
		update_post_meta( $post_id, self::PHONE_META, $phone );
		update_post_meta( $post_id, self::URL_META, $url );
		update_post_meta( $post_id, self::HOURS_META, $hours );
		update_post_meta( $post_id, self::SCOPE_META, $scope );
	}
}
