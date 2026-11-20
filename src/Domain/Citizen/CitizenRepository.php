<?php

declare(strict_types=1);

namespace App\Domain\Citizen;

use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Shared\ValueObject\Cpf;

interface CitizenRepository
{
    /**
     * Persists a new citizen and returns it with the generated id.
     *
     * @throws CitizenAlreadyExistsException
     */
    public function add(Citizen $citizen): Citizen;

    public function findByCpf(Cpf $cpf): ?Citizen;

    /**
     * Partial, case-insensitive match. The term is literal text: LIKE
     * wildcards (% and _) have no special meaning.
     *
     * @return list<Citizen> ordered by name
     */
    public function searchByName(string $term): array;

    public function count(): int;

    /**
     * @return list<Citizen> ordered by name
     */
    public function list(int $limit, int $offset): array;
}
