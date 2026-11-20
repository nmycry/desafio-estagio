<?php

declare(strict_types=1);

namespace App\Domain\Citizen;

use App\Domain\Shared\ValueObject\BrazilianState;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use DateTimeImmutable;

final readonly class Citizen
{
    public function __construct(
        public ?int $id,
        public FullName $name,
        public Cpf $cpf,
        public BrazilianState $state,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function register(
        FullName $name,
        Cpf $cpf,
        BrazilianState $state,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(null, $name, $cpf, $state, $createdAt);
    }

    #[\NoDiscard]
    public function withId(int $id): self
    {
        return clone($this, ['id' => $id]);
    }
}
