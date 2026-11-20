<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\InvalidCpfException;
use App\Domain\Shared\ValueObject\Cpf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CpfTest extends TestCase
{
    #[Test]
    public function itAcceptsAValidCpfWithoutMask(): void
    {
        $cpf = Cpf::fromString('52998224725');

        self::assertSame('52998224725', $cpf->value());
    }

    #[Test]
    public function itRemovesTheMask(): void
    {
        $cpf = Cpf::fromString(' 529.982.247-25 ');

        self::assertSame('52998224725', $cpf->value());
    }

    #[Test]
    public function itFormatsTheCpf(): void
    {
        self::assertSame('529.982.247-25', Cpf::fromString('52998224725')->formatted());
    }

    #[Test]
    public function itAcceptsACpfWhoseCheckDigitsAreZero(): void
    {
        self::assertSame('10000000108', Cpf::fromString('100.000.001-08')->value());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCpfs(): iterable
    {
        yield 'empty' => [''];
        yield 'too short' => ['5299822472'];
        yield 'too long' => ['529982247250'];
        yield 'letters only' => ['abcdefghijk'];
        yield 'wrong first check digit' => ['52998224735'];
        yield 'wrong second check digit' => ['52998224726'];
        yield 'repeated zeros' => ['000.000.000-00'];
        yield 'repeated ones' => ['111.111.111-11'];
        yield 'repeated nines' => ['99999999999'];
    }

    #[Test]
    #[DataProvider('invalidCpfs')]
    public function itRejectsInvalidCpfs(string $cpf): void
    {
        $this->expectException(InvalidCpfException::class);

        Cpf::fromString($cpf);
    }
}
