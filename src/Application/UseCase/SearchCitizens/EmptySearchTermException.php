<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchCitizens;

use App\Domain\Exception\DomainException;

final class EmptySearchTermException extends DomainException
{
    public static function create(): self
    {
        return new self('Informe um nome ou CPF para pesquisar.');
    }
}
