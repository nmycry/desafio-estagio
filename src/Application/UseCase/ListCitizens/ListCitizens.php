<?php

declare(strict_types=1);

namespace App\Application\UseCase\ListCitizens;

use App\Domain\Citizen\CitizenRepository;

final readonly class ListCitizens
{
    public const int PER_PAGE = 10;

    public function __construct(private CitizenRepository $citizens) {}

    /**
     * Pages outside the available range are clamped to the first or the last one.
     */
    public function handle(int $page): CitizenPage
    {
        $total = $this->citizens->count();
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $totalPages);

        return new CitizenPage(
            $this->citizens->list(self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            $page,
            $totalPages,
            $total,
        );
    }
}
