<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Citizen\Citizen;
use App\Domain\Shared\ValueObject\BrazilianState;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CitizenTest extends TestCase
{
    #[Test]
    public function itIsRegisteredWithoutIdAndReceivesOneWithoutMutation(): void
    {
        $citizen = Citizen::register(
            FullName::fromString('Maria Silva'),
            Cpf::fromString('52998224725'),
            BrazilianState::SP,
            new DateTimeImmutable('2026-01-01 10:00:00'),
        );

        $persisted = $citizen->withId(7);

        self::assertNull($citizen->id);
        self::assertSame(7, $persisted->id);
        self::assertSame($citizen->name, $persisted->name);
        self::assertSame($citizen->cpf, $persisted->cpf);
        self::assertSame($citizen->state, $persisted->state);
        self::assertSame($citizen->createdAt, $persisted->createdAt);
    }
}
