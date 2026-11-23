<?php
/**
 * Initial triage terms.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

/**
 * Starting lists for Category, Severity and Risk signal. Slugs are stable identifiers used by
 * Agents; names are shown in the site language and administrators may edit them.
 */
final class TriageTerms {

	/**
	 * Terms per taxonomy: slug => English source name.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function all(): array {
		return array(
			HelpRequestType::CATEGORY    => array(
				'emotional'      => __( 'Emotional', 'euteajudo' ),
				'relationships'  => __( 'Relationships', 'euteajudo' ),
				'work-study'     => __( 'Work and study', 'euteajudo' ),
				'financial'      => __( 'Financial', 'euteajudo' ),
				'material-needs' => __( 'Material needs', 'euteajudo' ),
				'health'         => __( 'Health', 'euteajudo' ),
				'violence'       => __( 'Violence', 'euteajudo' ),
				'other'          => __( 'Other', 'euteajudo' ),
			),
			HelpRequestType::SEVERITY    => array(
				'low'      => __( 'Low', 'euteajudo' ),
				'medium'   => __( 'Medium', 'euteajudo' ),
				'high'     => __( 'High', 'euteajudo' ),
				'critical' => __( 'Critical', 'euteajudo' ),
			),
			HelpRequestType::RISK_SIGNAL => array(
				'suicidal-ideation' => __( 'Suicidal ideation', 'euteajudo' ),
				'self-harm'         => __( 'Self-harm', 'euteajudo' ),
				'violence-suffered' => __( 'Violence suffered', 'euteajudo' ),
				'risk-to-others'    => __( 'Risk to others', 'euteajudo' ),
				'medical-emergency' => __( 'Medical emergency', 'euteajudo' ),
				'substance-crisis'  => __( 'Substance use crisis', 'euteajudo' ),
				'food-housing'      => __( 'Lack of food or housing', 'euteajudo' ),
				'possible-minor'    => __( 'Possibly under 18', 'euteajudo' ),
				'money-request'     => __( 'Money request', 'euteajudo' ),
				'personal-data'     => __( 'Personal data in the text', 'euteajudo' ),
			),
		);
	}

	/**
	 * Creates missing terms, named in the site language. Idempotent.
	 */
	public static function install(): void {
		$switched = switch_to_locale( get_locale() );
		foreach ( self::all() as $taxonomy => $terms ) {
			foreach ( $terms as $slug => $name ) {
				if ( ! term_exists( $slug, $taxonomy ) ) {
					wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				}
			}
		}
		if ( $switched ) {
			restore_previous_locale();
		}
	}
}
