<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\InvalidStateException;
use App\Domain\Shared\ValueObject\BrazilianState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BrazilianStateTest extends TestCase
{
    #[Test]
    public function itHasTheTwentySevenFederativeUnits(): void
    {
        self::assertCount(27, BrazilianState::cases());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validCodes(): iterable
    {
        yield 'uppercase' => ['SP'];
        yield 'lowercase' => ['sp'];
        yield 'surrounded by spaces' => [' Sp '];
    }

    #[Test]
    #[DataProvider('validCodes')]
    public function itAcceptsAValidCode(string $code): void
    {
        self::assertSame(BrazilianState::SP, BrazilianState::fromCode($code));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'empty' => [''];
        yield 'unknown code' => ['XX'];
        yield 'state name' => ['São Paulo'];
    }

    #[Test]
    #[DataProvider('invalidCodes')]
    public function itRejectsAnInvalidCode(string $code): void
    {
        $this->expectExceptionObject(InvalidStateException::invalid());

        BrazilianState::fromCode($code);
    }
}
