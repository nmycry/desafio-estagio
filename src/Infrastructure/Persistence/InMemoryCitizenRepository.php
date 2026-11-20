<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Citizen\Citizen;
use App\Domain\Citizen\CitizenRepository;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Shared\ValueObject\Cpf;

final class InMemoryCitizenRepository implements CitizenRepository
{
    /** @var array<string, Citizen> indexed by CPF digits */
    private array $citizens = [];

    public function add(Citizen $citizen): Citizen
    {
        if (isset($this->citizens[$citizen->cpf->value()])) {
            throw CitizenAlreadyExistsException::withCpf($citizen->cpf);
        }

        $persisted = $citizen->withId(count($this->citizens) + 1);
        $this->citizens[$citizen->cpf->value()] = $persisted;

        return $persisted;
    }

    public function findByCpf(Cpf $cpf): ?Citizen
    {
        return $this->citizens[$cpf->value()] ?? null;
    }

    public function searchByName(string $term): array
    {
        $matches = array_filter(
            $this->citizens,
            static fn(Citizen $citizen): bool => mb_stripos($citizen->name->value(), $term) !== false,
        );

        usort(
            $matches,
            static fn(Citizen $a, Citizen $b): int => strcmp($a->name->value(), $b->name->value()),
        );

        return $matches;
    }
}
