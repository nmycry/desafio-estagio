<?php

declare(strict_types=1);

namespace App\Application\UseCase\ListCitizens;

use App\Domain\Citizen\Citizen;

final readonly class CitizenPage
{
    /**
     * @param list<Citizen> $citizens
     */
    public function __construct(
        public array $citizens,
        public int $page,
        public int $totalPages,
        public int $total,
    ) {}

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->totalPages;
    }
}
