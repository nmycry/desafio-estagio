<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Shared\ValueObject\Cpf;

final class CitizenAlreadyExistsException extends DomainException
{
    public static function withCpf(Cpf $cpf): self
    {
        return new self(sprintf('Já existe um cidadão cadastrado com o CPF %s.', $cpf->formatted()));
    }
}
