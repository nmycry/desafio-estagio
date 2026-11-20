<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Exception\InvalidNameException;
use App\Domain\Shared\ValueObject\FullName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FullNameTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function validNames(): iterable
    {
        yield 'first and last name' => ['Maria Silva'];
        yield 'accents' => ['João Conceição'];
        yield 'apostrophe' => ["Joana D'Ávila"];
        yield 'hyphen' => ['Ana-Clara Souza'];
        yield 'several surnames' => ['Pedro de Alcântara Ferreira'];
    }

    #[Test]
    #[DataProvider('validNames')]
    public function itAcceptsValidNames(string $name): void
    {
        self::assertSame($name, FullName::fromString($name)->value());
    }

    #[Test]
    public function itTrimsAndCollapsesWhitespace(): void
    {
        $name = FullName::fromString("  Maria \t  da   Silva  ");

        self::assertSame('Maria da Silva', $name->value());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function initialsByName(): iterable
    {
        yield 'first and last name' => ['Maria Silva', 'MS'];
        yield 'middle names and prepositions' => ['Maria da Conceição Souza', 'MS'];
        yield 'accents' => ['érica ávila', 'ÉÁ'];
        yield 'hyphen and apostrophe' => ["Ana-Clara D'Ávila", 'AD'];
    }

    #[Test]
    #[DataProvider('initialsByName')]
    public function itReturnsTheInitialsOfTheFirstAndLastName(string $name, string $initials): void
    {
        self::assertSame($initials, FullName::fromString($name)->initials());
    }

    #[Test]
    public function itAcceptsTheMaximumLength(): void
    {
        $name = 'Ana ' . str_repeat('é', FullName::MAX_LENGTH - 4);

        self::assertSame($name, FullName::fromString($name)->value());
    }

    #[Test]
    public function itRejectsNamesLongerThanTheMaximum(): void
    {
        $this->expectExceptionObject(InvalidNameException::tooLong(FullName::MAX_LENGTH));

        FullName::fromString('Ana ' . str_repeat('é', FullName::MAX_LENGTH - 3));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function namesWithoutSurname(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'single word' => ['Maria'];
        yield 'surname without letters' => ['Maria -'];
    }

    #[Test]
    #[DataProvider('namesWithoutSurname')]
    public function itRequiresFirstAndLastName(string $name): void
    {
        $this->expectExceptionObject(InvalidNameException::missingSurname());

        FullName::fromString($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function namesWithInvalidCharacters(): iterable
    {
        yield 'digits' => ['Maria Silva 2'];
        yield 'symbols' => ['Maria @Silva'];
        yield 'html' => ['<b>Maria</b> Silva'];
    }

    #[Test]
    #[DataProvider('namesWithInvalidCharacters')]
    public function itRejectsInvalidCharacters(string $name): void
    {
        $this->expectExceptionObject(InvalidNameException::invalidCharacters());

        FullName::fromString($name);
    }
}
