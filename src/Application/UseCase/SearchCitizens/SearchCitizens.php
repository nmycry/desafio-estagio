<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchCitizens;

use App\Domain\Citizen\Citizen;
use App\Domain\Citizen\CitizenRepository;
use App\Domain\Exception\InvalidCpfException;
use App\Domain\Shared\ValueObject\Cpf;

final readonly class SearchCitizens
{
    private const int CPF_LENGTH = 11;

    public function __construct(private CitizenRepository $citizens) {}

    /**
     * A term with 11 digits (masked or not) is a CPF lookup; anything else
     * is a partial name search.
     *
     * @return list<Citizen>
     *
     * @throws EmptySearchTermException
     */
    public function handle(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            throw EmptySearchTermException::create();
        }

        if (strlen(Cpf::onlyDigits($term)) === self::CPF_LENGTH) {
            return $this->searchByCpf($term);
        }

        return $this->citizens->searchByName($term);
    }

    /**
     * @return list<Citizen>
     */
    private function searchByCpf(string $term): array
    {
        try {
            $citizen = $this->citizens->findByCpf(Cpf::fromString($term));
        } catch (InvalidCpfException) {
            return [];
        }

        return $citizen === null ? [] : [$citizen];
    }
}
