<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchCitizens;

use InvalidArgumentException;

final class EmptySearchTermException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new self('Informe um nome ou CPF para pesquisar.');
    }
}
