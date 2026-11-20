<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidStateException extends DomainException
{
    public static function invalid(): self
    {
        return new self('Selecione um estado válido.');
    }
}
