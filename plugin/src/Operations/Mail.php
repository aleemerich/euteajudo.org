<?php
/**
 * Outgoing email.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Operations;

use EuTeAjudo\Settings;

/**
 * Sends email through the transactional service's SMTP when EUTEAJUDO_SMTP_HOST is set
 * (production), with a neutral sender. Without it, PHP's mail() is used (local: Mailpit).
 *
 * Settings: EUTEAJUDO_SMTP_HOST, EUTEAJUDO_SMTP_PORT (587), EUTEAJUDO_SMTP_USER,
 * EUTEAJUDO_SMTP_PASSWORD, EUTEAJUDO_MAIL_FROM (no-reply address of the site's domain).
 */
final class Mail {

	public const FROM_NAME = 'EuTeAjudo';

	/**
	 * Hooks the mailer.
	 */
	public static function register(): void {
		add_filter( 'wp_mail_from', array( self::class, 'from' ) );
		add_filter( 'wp_mail_from_name', array( self::class, 'from_name' ) );
		add_action( 'phpmailer_init', array( self::class, 'configure' ) );
	}

	/**
	 * Sender address.
	 *
	 * @param string $from Default sender.
	 */
	public static function from( string $from ): string {
		$configured = Settings::get( 'EUTEAJUDO_MAIL_FROM' );
		if ( is_email( $configured ) ) {
			return $configured;
		}
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		return is_string( $host ) && '' !== $host ? 'nao-responda@' . preg_replace( '/^www\./', '', $host ) : $from;
	}

	/**
	 * Sender name: the site, never a person.
	 */
	public static function from_name(): string {
		return self::FROM_NAME;
	}

	/**
	 * Points PHPMailer at the SMTP service.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $mailer Mailer.
	 */
	public static function configure( \PHPMailer\PHPMailer\PHPMailer $mailer ): void {
		$host = Settings::get( 'EUTEAJUDO_SMTP_HOST' );
		if ( '' === $host ) {
			return;
		}
		$port = (int) Settings::get( 'EUTEAJUDO_SMTP_PORT' );
		$port = $port > 0 ? $port : 587;
		$user = Settings::get( 'EUTEAJUDO_SMTP_USER' );
		$mailer->isSMTP();
		$mailer->Host       = $host; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->Port       = $port; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->SMTPSecure = 465 === $port ? 'ssl' : 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->SMTPAuth   = '' !== $user; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->Username   = $user; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$mailer->Password   = Settings::get( 'EUTEAJUDO_SMTP_PASSWORD' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
	}
}
