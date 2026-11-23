<?php
/**
 * Unit tests for personal data masking.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit\HelpRequests;

use EuTeAjudo\HelpRequests\PersonalDataMask;
use PHPUnit\Framework\TestCase;

/**
 * Phones, emails, CPF, CEP and links are removed; ordinary numbers stay.
 */
final class PersonalDataMaskTest extends TestCase {

	private const R = '[removed]';

	/**
	 * Masks with a short placeholder.
	 *
	 * @param string $text Text.
	 */
	private function mask( string $text ): string {
		return ( new PersonalDataMask( self::R ) )->mask( $text );
	}

	/**
	 * Texts with personal data and the expected result.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function personal_data(): array {
		return array(
			'email'                 => array( 'me escreve em maria.silva+x@gmail.com depois', 'me escreve em [removed] depois' ),
			'mobile with area code' => array( 'meu zap (11) 91234-5678 ok', 'meu zap [removed] ok' ),
			'mobile plain'          => array( 'liga 11912345678', 'liga [removed]' ),
			'mobile with country'   => array( 'numero +55 11 91234 5678.', 'numero [removed].' ),
			'landline'              => array( 'fixo 3333-4444 de casa', 'fixo [removed] de casa' ),
			'cpf formatted'         => array( 'cpf 123.456.789-09', 'cpf [removed]' ),
			'cpf plain'             => array( 'cpf 12345678909', 'cpf [removed]' ),
			'cep'                   => array( 'moro no cep 01310-100', 'moro no cep [removed]' ),
			'https link'            => array( 'veja https://instagram.com/maria_silva', 'veja [removed]' ),
			'www link'              => array( 'olha www.facebook.com/maria', 'olha [removed]' ),
			'bare domain with path' => array( 'meu perfil instagram.com/maria_s', 'meu perfil [removed]' ),
			'two items'             => array( 'a@b.co e 99999-8888', '[removed] e [removed]' ),
		);
	}

	/**
	 * Masks each kind of personal data.
	 *
	 * @dataProvider personal_data
	 *
	 * @param string $text     Input.
	 * @param string $expected Output.
	 */
	public function test_masks_personal_data( string $text, string $expected ): void {
		$this->assertSame( $expected, $this->mask( $text ) );
	}

	/**
	 * Ordinary numbers and text that must stay as written.
	 *
	 * @return array<string, array{0: string}>
	 */
	public static function ordinary_text(): array {
		return array(
			'age'           => array( 'tenho 35 anos e 2 filhos' ),
			'year'          => array( 'desde 2019 estou assim' ),
			'date'          => array( 'fui demitido em 22/11/2026' ),
			'money'         => array( 'devo R$ 1.500,00 de aluguel' ),
			'time'          => array( 'acordo às 3h, às vezes 03:30' ),
			'percent'       => array( 'gasto 70% do salário' ),
			'sentence dots' => array( 'não sei.mais o que fazer...sério' ),
			'accents'       => array( 'Não consigo dormir, é difícil' ),
		);
	}

	/**
	 * Leaves ordinary text untouched.
	 *
	 * @dataProvider ordinary_text
	 *
	 * @param string $text Input.
	 */
	public function test_keeps_ordinary_text( string $text ): void {
		$this->assertSame( $text, $this->mask( $text ) );
	}

	public function test_reports_whether_anything_was_masked(): void {
		$mask = new PersonalDataMask( self::R );

		$this->assertTrue( $mask->contains_personal_data( 'liga 11912345678' ) );
		$this->assertFalse( $mask->contains_personal_data( 'tenho 35 anos' ) );
	}
}
