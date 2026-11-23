<?php
/**
 * Public content pages: About, Terms of use, Privacy policy.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Site;

/**
 * Created once from plugin/content/<locale>/*.html and then edited in wp-admin like any page.
 * The privacy page becomes the site's privacy policy page (Settings > Privacy). Fields marked
 * "[preencher: ...]" need the maintainer before launch; the dashboard shows a notice meanwhile.
 */
final class ContentPages {

	public const OPTION = 'eta_content_pages';

	public const ABOUT   = 'about';
	public const TERMS   = 'terms';
	public const PRIVACY = 'privacy';
	public const KEYS    = array( self::ABOUT, self::TERMS, self::PRIVACY );

	public const PLACEHOLDER = '[preencher';

	/**
	 * Address of each page in Portuguese, the site language.
	 */
	private const SLUGS = array(
		self::ABOUT   => 'sobre',
		self::TERMS   => 'termos-de-uso',
		self::PRIVACY => 'privacidade',
	);

	/**
	 * Hooks the dashboard notice.
	 */
	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'placeholder_notice' ) );
	}

	/**
	 * Creates missing pages. Idempotent.
	 */
	public static function install(): void {
		$ids      = self::ids();
		$switched = switch_to_locale( get_locale() );
		foreach ( self::KEYS as $key ) {
			$existing = isset( $ids[ $key ] ) ? get_post( $ids[ $key ] ) : null;
			if ( $existing instanceof \WP_Post && 'trash' !== $existing->post_status ) {
				continue;
			}
			$page_id = wp_insert_post(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_title'     => self::title( $key ),
					'post_name'      => self::SLUGS[ $key ],
					'post_content'   => self::content( $key ),
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
			if ( is_int( $page_id ) && $page_id > 0 ) {
				$ids[ $key ] = $page_id;
			}
		}
		if ( $switched ) {
			restore_previous_locale();
		}
		update_option( self::OPTION, $ids, false );
		if ( isset( $ids[ self::PRIVACY ] ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids[ self::PRIVACY ] );
		}
	}

	/**
	 * Page URL, or an empty string.
	 *
	 * @param string $key Page key.
	 */
	public static function url( string $key ): string {
		$id = self::ids()[ $key ] ?? 0;
		if ( $id <= 0 || 'publish' !== get_post_status( $id ) ) {
			return '';
		}
		$url = get_permalink( $id );
		return is_string( $url ) ? $url : '';
	}

	/**
	 * Translated page title.
	 *
	 * @param string $key Page key.
	 */
	public static function title( string $key ): string {
		return match ( $key ) {
			self::ABOUT   => __( 'About', 'euteajudo' ),
			self::TERMS   => __( 'Terms of use', 'euteajudo' ),
			default       => __( 'Privacy policy', 'euteajudo' ),
		};
	}

	/**
	 * Pages that still have fields for the maintainer to fill in.
	 *
	 * @return array<string, int> Key => page ID.
	 */
	public static function with_placeholders(): array {
		$found = array();
		foreach ( self::ids() as $key => $id ) {
			if ( str_contains( get_post( $id )->post_content ?? '', self::PLACEHOLDER ) ) {
				$found[ $key ] = $id;
			}
		}
		return $found;
	}

	/**
	 * Reminds administrators of unfinished legal pages.
	 */
	public static function placeholder_notice(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$pending = self::with_placeholders();
		if ( array() === $pending ) {
			return;
		}
		$links = array_map(
			static fn ( string $key, int $id ): string => '<a href="' . esc_url( (string) get_edit_post_link( $id ) ) . '">' . esc_html( self::title( $key ) ) . '</a>',
			array_keys( $pending ),
			array_values( $pending )
		);
		/* translators: %s: links to pages. */
		printf( '<div class="notice notice-warning"><p>%s</p></div>', wp_kses_post( sprintf( __( 'EuTeAjudo: fill in the fields marked "[preencher: ...]" before launch: %s.', 'euteajudo' ), implode( ', ', $links ) ) ) );
	}

	/**
	 * Initial content for the site language (Portuguese when there is no file for it).
	 *
	 * @param string $key Page key.
	 */
	private static function content( string $key ): string {
		$dir  = dirname( \EuTeAjudo\PLUGIN_FILE ) . '/content/';
		$file = $dir . get_locale() . '/' . $key . '.html';
		if ( ! is_readable( $file ) ) {
			$file = $dir . 'pt_BR/' . $key . '.html';
		}
		$content = is_readable( $file ) ? file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin file.
		return is_string( $content ) ? $content : '';
	}

	/**
	 * Stored page IDs.
	 *
	 * @return array<string, int>
	 */
	private static function ids(): array {
		$ids = get_option( self::OPTION, array() );
		return is_array( $ids ) ? array_map( 'intval', array_intersect_key( $ids, array_flip( self::KEYS ) ) ) : array();
	}
}
