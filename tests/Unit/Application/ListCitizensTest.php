<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\UseCase\ListCitizens\ListCitizens;
use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Domain\Citizen\Citizen;
use App\Infrastructure\Persistence\CitizenSeeder;
use App\Infrastructure\Persistence\InMemoryCitizenRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListCitizensTest extends TestCase
{
    private InMemoryCitizenRepository $citizens;

    private ListCitizens $listCitizens;

    protected function setUp(): void
    {
        $this->citizens = new InMemoryCitizenRepository();
        $this->listCitizens = new ListCitizens($this->citizens);
    }

    #[Test]
    public function itReturnsTheFirstPageOrderedByName(): void
    {
        $this->seed();

        $page = $this->listCitizens->handle(1);

        self::assertCount(ListCitizens::PER_PAGE, $page->citizens);
        self::assertSame(1, $page->page);
        self::assertSame(3, $page->totalPages);
        self::assertSame(25, $page->total);
        self::assertFalse($page->hasPrevious());
        self::assertTrue($page->hasNext());

        $names = $this->names($page->citizens);
        $sorted = $names;
        sort($sorted);
        self::assertSame($sorted, $names);
    }

    #[Test]
    public function itReturnsAPartialLastPage(): void
    {
        $this->seed();

        $page = $this->listCitizens->handle(3);

        self::assertCount(5, $page->citizens);
        self::assertTrue($page->hasPrevious());
        self::assertFalse($page->hasNext());
    }

    #[Test]
    public function itNeverRepeatsACitizenAcrossPages(): void
    {
        $this->seed();

        $names = array_merge(
            $this->names($this->listCitizens->handle(1)->citizens),
            $this->names($this->listCitizens->handle(2)->citizens),
            $this->names($this->listCitizens->handle(3)->citizens),
        );

        self::assertCount(25, array_unique($names));
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function outOfRangePages(): iterable
    {
        yield 'zero' => [0, 1];
        yield 'negative' => [-5, 1];
        yield 'after the last one' => [99, 3];
    }

    #[Test]
    #[DataProvider('outOfRangePages')]
    public function itClampsPagesOutsideTheAvailableRange(int $requested, int $expected): void
    {
        $this->seed();

        self::assertSame($expected, $this->listCitizens->handle($requested)->page);
    }

    #[Test]
    public function itReturnsASingleEmptyPageWhenNoCitizenIsRegistered(): void
    {
        $page = $this->listCitizens->handle(1);

        self::assertSame([], $page->citizens);
        self::assertSame(1, $page->totalPages);
        self::assertSame(0, $page->total);
        self::assertFalse($page->hasNext());
    }

    private function seed(): void
    {
        new CitizenSeeder(new RegisterCitizen($this->citizens))->run();
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
