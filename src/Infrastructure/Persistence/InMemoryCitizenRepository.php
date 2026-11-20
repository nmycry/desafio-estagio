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
        return $this->sortedByName(array_filter(
            $this->citizens,
            static fn(Citizen $citizen): bool => mb_stripos($citizen->name->value(), $term) !== false,
        ));
    }

    public function count(): int
    {
        return count($this->citizens);
    }

    public function list(int $limit, int $offset): array
    {
        return array_slice($this->sortedByName($this->citizens), $offset, $limit);
    }

    /**
     * @param array<Citizen> $citizens
     *
     * @return list<Citizen>
     */
    private function sortedByName(array $citizens): array
    {
        usort(
            $citizens,
            static fn(Citizen $a, Citizen $b): int => [$a->name->value(), $a->id] <=> [$b->name->value(), $b->id],
        );

        return $citizens;
    }
}
