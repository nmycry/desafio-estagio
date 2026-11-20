<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidNameException extends DomainException
{
    public static function missingSurname(): self
    {
        return new self('Informe o nome completo (nome e sobrenome).');
    }

    public static function tooLong(int $maxLength): self
    {
        return new self(sprintf('O nome deve ter no máximo %d caracteres.', $maxLength));
    }

    public static function invalidCharacters(): self
    {
        return new self('O nome deve conter apenas letras, espaços, apóstrofo e hífen.');
    }
}
