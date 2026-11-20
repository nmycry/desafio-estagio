<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Infrastructure\Persistence\CitizenSeeder;
use App\Infrastructure\Persistence\InMemoryCitizenRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CitizenSeederTest extends TestCase
{
    private InMemoryCitizenRepository $citizens;

    private CitizenSeeder $seeder;

    protected function setUp(): void
    {
        $this->citizens = new InMemoryCitizenRepository();
        $this->seeder = new CitizenSeeder(new RegisterCitizen($this->citizens));
    }

    #[Test]
    public function itRegistersEverySampleCitizen(): void
    {
        self::assertSame(['created' => 25, 'skipped' => 0], $this->seeder->run());
        self::assertSame(25, $this->citizens->count());
    }

    #[Test]
    public function itSkipsCitizensAlreadyRegisteredWhenRunAgain(): void
    {
        $this->seeder->run();

        self::assertSame(['created' => 0, 'skipped' => 25], $this->seeder->run());
        self::assertSame(25, $this->citizens->count());
    }

    #[Test]
    public function itHasSeveralCitizensSharingASurnameForTheNameSearch(): void
    {
        $this->seeder->run();

        self::assertGreaterThan(1, count($this->citizens->searchByName('Silva')));
        self::assertGreaterThan(1, count($this->citizens->searchByName('Souza')));
    }
}
