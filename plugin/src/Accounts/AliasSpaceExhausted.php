<?php
/**
 * Raised when no free alias number is found.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * No free alias number after the maximum number of attempts.
 */
final class AliasSpaceExhausted extends \RuntimeException {
}
