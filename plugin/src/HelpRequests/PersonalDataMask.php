<?php
/**
 * Personal data masking.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

/**
 * Replaces emails, links, CPF, CEP and phone numbers with a placeholder before text is stored,
 * so nothing written in a help request or reply can identify the person.
 *
 * Regular expressions cover the formats people actually type in Brazil; a street address written
 * out in words is not detected (known limit, see docs/mvp.md). Pure PHP, fully unit-tested.
 */
final class PersonalDataMask {

	/**
	 * Patterns, applied in order. Emails come before links, links before numbers.
	 */
	private const PATTERNS = array(
		// Email address.
		'/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/u',
		// Link with scheme or www, without trailing punctuation.
		'/\b(?:https?:\/\/|www\.)\S*[^\s.,;:!?)]/iu',
		// Bare domain followed by a path, e.g. instagram.com/name.
		'/\b[a-z0-9\-]+(?:\.[a-z0-9\-]+)*\.(?:com|net|org|br|me|io|co|info|app|social)(?:\.[a-z]{2})?\/\S*[^\s.,;:!?)]/iu',
		// CPF with punctuation.
		'/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/u',
		// CEP.
		'/\b\d{5}-\d{3}\b/u',
		// Phone numbers (and other long digit runs such as a plain CPF): optional country code,
		// optional area code, then 4 or 5 digits and 4 digits, with spaces, dots or hyphens.
		'/(?<![\w])(?:\+\d{1,3}[\s.\-]?)?(?:\(?\d{2}\)?[\s.\-]?)?\d{4,5}[\s.\-]?\d{4}(?![\w])/u',
	);

	/**
	 * Creates a mask.
	 *
	 * @param string $placeholder Text that replaces each match, e.g. "[dado pessoal removido]".
	 */
	public function __construct( private readonly string $placeholder ) {}

	/**
	 * Returns the text with personal data replaced.
	 *
	 * @param string $text Text.
	 */
	public function mask( string $text ): string {
		foreach ( self::PATTERNS as $pattern ) {
			$text = (string) preg_replace( $pattern, $this->placeholder, $text );
		}
		return $text;
	}

	/**
	 * Whether the text contains anything this mask would replace.
	 *
	 * @param string $text Text.
	 */
	public function contains_personal_data( string $text ): bool {
		return $this->mask( $text ) !== $text;
	}
}
