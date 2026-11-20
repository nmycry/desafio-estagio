<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidCpfException extends DomainException
{
    public static function invalid(): self
    {
        return new self('CPF inválido. Verifique os números digitados.');
    }
}
