<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * Base for expected business errors whose message can be shown to the user.
 */
abstract class DomainException extends \DomainException {}
