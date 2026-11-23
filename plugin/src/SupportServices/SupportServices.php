<?php
/**
 * Support service catalogue.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\SupportServices;

use EuTeAjudo\HelpRequests\HelpRequestType;

/**
 * The initial catalogue and the services that match a triage.
 */
final class SupportServices {

	public const MAX_CANDIDATES = 8;

	/**
	 * Creates the initial national services when the catalogue is empty. Administrators edit
	 * or add services afterwards in wp-admin.
	 */
	public static function install(): void {
		$existing = get_posts(
			array(
				'post_type'   => SupportServiceType::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		if ( array() !== $existing ) {
			return;
		}
		$switched = switch_to_locale( get_locale() );
		foreach ( self::initial() as $service ) {
			$id = wp_insert_post(
				array(
					'post_type'    => SupportServiceType::POST_TYPE,
					'post_status'  => 'publish',
					'post_title'   => $service['name'],
					'post_content' => $service['description'],
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				continue;
			}
			SupportServiceType::save_fields( $id, $service['phone'], $service['url'], $service['hours'], $service['scope'] );
			wp_set_object_terms( $id, $service['categories'], HelpRequestType::CATEGORY );
			wp_set_object_terms( $id, $service['risk_signals'], HelpRequestType::RISK_SIGNAL );
		}
		if ( $switched ) {
			restore_previous_locale();
		}
	}

	/**
	 * Published services sharing a risk signal or category with the triage; risk matches first.
	 *
	 * @param string[] $categories   Category slugs.
	 * @param string[] $risk_signals Risk signal slugs.
	 * @return list<array<string, mixed>>
	 */
	public static function candidates( array $categories, array $risk_signals ): array {
		if ( array() === $categories && array() === $risk_signals ) {
			return array();
		}
		$query = array( 'relation' => 'OR' );
		foreach ( array(
			HelpRequestType::RISK_SIGNAL => $risk_signals,
			HelpRequestType::CATEGORY    => $categories,
		) as $taxonomy => $slugs ) {
			if ( array() !== $slugs ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slugs,
				);
			}
		}
		$posts    = get_posts(
			array(
				'post_type'   => SupportServiceType::POST_TYPE,
				'post_status' => 'publish',
				'numberposts' => 50,
				'tax_query'   => $query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Small catalogue.
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);
		$services = array();
		foreach ( $posts as $post ) {
			$risks      = self::slugs( $post->ID, HelpRequestType::RISK_SIGNAL );
			$services[] = array(
				'id'           => $post->ID,
				'name'         => $post->post_title,
				'description'  => wp_strip_all_tags( $post->post_content ),
				'phone'        => (string) get_post_meta( $post->ID, SupportServiceType::PHONE_META, true ),
				'url'          => (string) get_post_meta( $post->ID, SupportServiceType::URL_META, true ),
				'hours'        => (string) get_post_meta( $post->ID, SupportServiceType::HOURS_META, true ),
				'scope'        => (string) get_post_meta( $post->ID, SupportServiceType::SCOPE_META, true ),
				'categories'   => self::slugs( $post->ID, HelpRequestType::CATEGORY ),
				'risk_signals' => $risks,
				'matches_risk' => array() !== array_intersect( $risks, $risk_signals ),
			);
		}
		usort( $services, static fn ( array $a, array $b ): int => (int) $b['matches_risk'] <=> (int) $a['matches_risk'] );
		return array_slice( $services, 0, self::MAX_CANDIDATES );
	}

	/**
	 * Initial catalogue: national phone lines and one digital service.
	 *
	 * @return list<array{name: string, description: string, phone: string, url: string, hours: string, scope: string, categories: string[], risk_signals: string[]}>
	 */
	private static function initial(): array {
		$always = __( '24 hours, every day', 'euteajudo' );
		return array(
			array(
				'name'         => 'CVV – Centro de Valorização da Vida',
				'description'  => __( 'Emotional support and suicide prevention, free and confidential, by phone, chat and email.', 'euteajudo' ),
				'phone'        => '188',
				'url'          => 'https://cvv.org.br',
				'hours'        => $always,
				'scope'        => 'national',
				'categories'   => array( 'emotional' ),
				'risk_signals' => array( 'suicidal-ideation', 'self-harm' ),
			),
			array(
				'name'         => 'SAMU',
				'description'  => __( 'Mobile emergency medical service.', 'euteajudo' ),
				'phone'        => '192',
				'url'          => '',
				'hours'        => $always,
				'scope'        => 'national',
				'categories'   => array( 'health' ),
				'risk_signals' => array( 'medical-emergency', 'substance-crisis', 'self-harm' ),
			),
			array(
				'name'         => 'Polícia Militar',
				'description'  => __( 'Police emergency, for violence happening now or immediate danger.', 'euteajudo' ),
				'phone'        => '190',
				'url'          => '',
				'hours'        => $always,
				'scope'        => 'national',
				'categories'   => array( 'violence' ),
				'risk_signals' => array( 'violence-suffered', 'risk-to-others' ),
			),
			array(
				'name'         => 'Ligue 180 – Central de Atendimento à Mulher',
				'description'  => __( 'Guidance and reports about violence against women, free and confidential.', 'euteajudo' ),
				'phone'        => '180',
				'url'          => '',
				'hours'        => $always,
				'scope'        => 'national',
				'categories'   => array( 'violence' ),
				'risk_signals' => array( 'violence-suffered' ),
			),
			array(
				'name'         => 'Disque 100 – Direitos Humanos',
				'description'  => __( 'Reports of human rights violations, including against children, older people and people with disabilities.', 'euteajudo' ),
				'phone'        => '100',
				'url'          => '',
				'hours'        => $always,
				'scope'        => 'national',
				'categories'   => array( 'violence' ),
				'risk_signals' => array( 'violence-suffered', 'possible-minor' ),
			),
			array(
				'name'         => 'Pode Falar',
				'description'  => __( 'Online emotional support for teenagers and young people, by UNICEF and partners.', 'euteajudo' ),
				'phone'        => '',
				'url'          => 'https://www.podefalar.org.br',
				'hours'        => '',
				'scope'        => 'digital',
				'categories'   => array( 'emotional' ),
				'risk_signals' => array( 'possible-minor' ),
			),
		);
	}

	/**
	 * Term slugs of a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string[]
	 */
	private static function slugs( int $post_id, string $taxonomy ): array {
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
		return is_array( $terms ) ? array_values( array_map( 'strval', $terms ) ) : array();
	}
}
