<?php

declare(strict_types=1);

namespace App\Application\UseCase\RegisterCitizen;

use App\Domain\Citizen\Citizen;
use App\Domain\Citizen\CitizenRepository;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Exception\InvalidCpfException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\InvalidStateException;
use App\Domain\Shared\ValueObject\BrazilianState;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use DateTimeImmutable;

final readonly class RegisterCitizen
{
    public function __construct(private CitizenRepository $citizens) {}

    /**
     * @throws InvalidNameException
     * @throws InvalidCpfException
     * @throws InvalidStateException
     * @throws CitizenAlreadyExistsException
     */
    public function handle(RegisterCitizenInput $input): Citizen
    {
        $name = FullName::fromString($input->name);
        $cpf = Cpf::fromString($input->cpf);
        $state = trim($input->state) === '' ? null : BrazilianState::fromCode($input->state);

        if ($this->citizens->findByCpf($cpf) !== null) {
            throw CitizenAlreadyExistsException::withCpf($cpf);
        }

        return $this->citizens->add(Citizen::register($name, $cpf, $state, new DateTimeImmutable()));
    }
}
