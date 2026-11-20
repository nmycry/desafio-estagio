<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\Citizen\Citizen;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Shared\ValueObject\BrazilianState;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use App\Infrastructure\Config\Config;
use App\Infrastructure\Persistence\Connection;
use App\Infrastructure\Persistence\PdoCitizenRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PdoCitizenRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoCitizenRepository $repository;

    protected function setUp(): void
    {
        $config = Config::fromEnvironment();
        $this->pdo = Connection::create($config, $config->dbTestName);
        $this->pdo->exec('TRUNCATE TABLE citizens');
        $this->repository = new PdoCitizenRepository($this->pdo);
    }

    #[Test]
    public function itAddsACitizenAndStoresOnlyCpfDigits(): void
    {
        $citizen = $this->repository->add($this->citizen('Maria Silva', '529.982.247-25', BrazilianState::RJ));

        $statement = $this->pdo->prepare('SELECT cpf, state FROM citizens WHERE id = :id');
        $statement->execute(['id' => $citizen->id]);

        self::assertSame(['cpf' => '52998224725', 'state' => 'RJ'], $statement->fetch());
    }

    #[Test]
    public function itFindsACitizenByCpf(): void
    {
        $added = $this->repository->add($this->citizen('Maria Silva', '52998224725', BrazilianState::RJ));

        $found = $this->repository->findByCpf(Cpf::fromString('52998224725'));

        self::assertNotNull($found);
        self::assertSame($added->id, $found->id);
        self::assertSame('Maria Silva', $found->name->value());
        self::assertSame('529.982.247-25', $found->cpf->formatted());
        self::assertSame(BrazilianState::RJ, $found->state);
        self::assertEquals($added->createdAt, $found->createdAt);
    }

    #[Test]
    public function itReturnsNullWhenTheCpfIsNotRegistered(): void
    {
        self::assertNull($this->repository->findByCpf(Cpf::fromString('52998224725')));
    }

    #[Test]
    public function itSearchesByPartialNameIgnoringCaseAndAccents(): void
    {
        $this->repository->add($this->citizen('João Souza', '52998224725'));
        $this->repository->add($this->citizen('Ana Silveira', '11144477735'));
        $this->repository->add($this->citizen('Joana Silva', '10000000108'));

        self::assertSame(['Joana Silva', 'João Souza'], $this->names($this->repository->searchByName('JOA')));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function wildcardTerms(): iterable
    {
        yield 'percent' => ['%'];
        yield 'underscore' => ['_'];
        yield 'underscore inside a name' => ['Mar_a'];
        yield 'escape character' => ['!'];
    }

    #[Test]
    #[DataProvider('wildcardTerms')]
    public function itTreatsLikeWildcardsAsLiteralText(string $term): void
    {
        $this->repository->add($this->citizen('Maria Silva', '52998224725'));

        self::assertSame([], $this->repository->searchByName($term));
    }

    #[Test]
    public function itConvertsTheUniqueViolationIntoADomainException(): void
    {
        $this->repository->add($this->citizen('Maria Silva', '52998224725'));

        $this->expectException(CitizenAlreadyExistsException::class);

        $this->repository->add($this->citizen('João Souza', '52998224725'));
    }

    private function citizen(string $name, string $cpf, BrazilianState $state = BrazilianState::SP): Citizen
    {
        return Citizen::register(
            FullName::fromString($name),
            Cpf::fromString($cpf),
            $state,
            new DateTimeImmutable('2026-01-01 10:00:00'),
        );
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
