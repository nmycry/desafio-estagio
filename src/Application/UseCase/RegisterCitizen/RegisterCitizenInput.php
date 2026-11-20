<?php

declare(strict_types=1);

namespace App\Application\UseCase\RegisterCitizen;

final readonly class RegisterCitizenInput
{
    public function __construct(
        public string $name,
        public string $cpf,
    ) {}
}
