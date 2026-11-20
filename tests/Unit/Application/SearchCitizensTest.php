<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\RegisterCitizen\RegisterCitizenInput;
use App\Application\UseCase\SearchCitizens\EmptySearchTermException;
use App\Application\UseCase\SearchCitizens\SearchCitizens;
use App\Domain\Citizen\Citizen;
use App\Infrastructure\Persistence\InMemoryCitizenRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SearchCitizensTest extends TestCase
{
    private SearchCitizens $searchCitizens;

    protected function setUp(): void
    {
        $citizens = new InMemoryCitizenRepository();
        $registerCitizen = new RegisterCitizen($citizens);

        $registerCitizen->handle(new RegisterCitizenInput('Maria Silva', '52998224725', 'SP'));
        $registerCitizen->handle(new RegisterCitizenInput('Ana Silveira', '11144477735', 'MG'));
        $registerCitizen->handle(new RegisterCitizenInput('João Souza', '10000000108', 'BA'));

        $this->searchCitizens = new SearchCitizens($citizens);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function cpfTerms(): iterable
    {
        yield 'without mask' => ['52998224725'];
        yield 'with mask' => ['529.982.247-25'];
    }

    #[Test]
    #[DataProvider('cpfTerms')]
    public function itFindsACitizenByCpf(string $term): void
    {
        self::assertSame(['Maria Silva'], $this->names($this->searchCitizens->handle($term)));
    }

    #[Test]
    public function itFindsCitizensByPartialNameIgnoringCase(): void
    {
        self::assertSame(['João Souza'], $this->names($this->searchCitizens->handle('  SOUZ ')));
    }

    #[Test]
    public function itReturnsEveryMatchOrderedByName(): void
    {
        self::assertSame(
            ['Ana Silveira', 'Maria Silva'],
            $this->names($this->searchCitizens->handle('silv')),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function termsWithoutMatch(): iterable
    {
        yield 'unknown name' => ['Pedro'];
        yield 'unknown valid cpf' => ['863.054.250-00'];
        yield 'eleven digits that are not a valid cpf' => ['12345678900'];
        yield 'percent wildcard' => ['%'];
        yield 'underscore wildcard' => ['Mar_a'];
    }

    #[Test]
    #[DataProvider('termsWithoutMatch')]
    public function itReturnsNothingWhenNoCitizenMatches(string $term): void
    {
        self::assertSame([], $this->searchCitizens->handle($term));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyTerms(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ["  \t "];
    }

    #[Test]
    #[DataProvider('emptyTerms')]
    public function itRejectsAnEmptyTerm(string $term): void
    {
        $this->expectException(EmptySearchTermException::class);

        $this->searchCitizens->handle($term);
    }

    /**
     * @param list<Citizen> $citizens
     *
     * @return list<string>
     */
    private function names(array $citizens): array
    {
        return array_map(static fn(Citizen $citizen): string => $citizen->name->value(), $citizens);
    }
}
