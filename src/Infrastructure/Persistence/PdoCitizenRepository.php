<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Citizen\Citizen;
use App\Domain\Citizen\CitizenRepository;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Shared\ValueObject\BrazilianState;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use DateTimeImmutable;
use PDO;
use PDOException;

/**
 * @phpstan-type CitizenRow array{id: int, name: string, cpf: string, state: string, created_at: string}
 */
final readonly class PdoCitizenRepository implements CitizenRepository
{
    private const string SELECT_CITIZENS = 'SELECT id, name, cpf, state, created_at FROM citizens';

    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    private const int MYSQL_DUPLICATE_ENTRY = 1062;

    public function __construct(private PDO $pdo) {}

    public function add(Citizen $citizen): Citizen
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO citizens (name, cpf, state, created_at) VALUES (:name, :cpf, :state, :created_at)',
        );

        try {
            $statement->execute([
                'name' => $citizen->name->value(),
                'cpf' => $citizen->cpf->value(),
                'state' => $citizen->state->value,
                'created_at' => $citizen->createdAt->format(self::DATETIME_FORMAT),
            ]);
        } catch (PDOException $exception) {
            if ($this->isDuplicateEntry($exception)) {
                throw CitizenAlreadyExistsException::withCpf($citizen->cpf);
            }

            throw $exception;
        }

        return $citizen->withId((int) $this->pdo->lastInsertId());
    }

    public function findByCpf(Cpf $cpf): ?Citizen
    {
        $statement = $this->pdo->prepare(self::SELECT_CITIZENS . ' WHERE cpf = :cpf');
        $statement->execute(['cpf' => $cpf->value()]);

        /** @var CitizenRow|false $row */
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function searchByName(string $term): array
    {
        $statement = $this->pdo->prepare(
            self::SELECT_CITIZENS . " WHERE name LIKE :pattern ESCAPE '!' ORDER BY name, id",
        );
        $statement->execute(['pattern' => '%' . $this->escapeLike($term) . '%']);

        /** @var list<CitizenRow> $rows */
        $rows = $statement->fetchAll();

        return array_map($this->hydrate(...), $rows);
    }

    public function count(): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM citizens');
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    public function list(int $limit, int $offset): array
    {
        $statement = $this->pdo->prepare(self::SELECT_CITIZENS . ' ORDER BY name, id LIMIT :limit OFFSET :offset');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        /** @var list<CitizenRow> $rows */
        $rows = $statement->fetchAll();

        return array_map($this->hydrate(...), $rows);
    }

    /**
     * Makes the wildcards (% and _) and the escape character itself match
     * literally inside a LIKE pattern declared with ESCAPE '!'.
     */
    private function escapeLike(string $term): string
    {
        return strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']);
    }

    private function isDuplicateEntry(PDOException $exception): bool
    {
        return $exception->getCode() === '23000'
            && ($exception->errorInfo[1] ?? null) === self::MYSQL_DUPLICATE_ENTRY;
    }

    /**
     * @param CitizenRow $row
     */
    private function hydrate(array $row): Citizen
    {
        return new Citizen(
            $row['id'],
            FullName::fromString($row['name']),
            Cpf::fromString($row['cpf']),
            BrazilianState::from($row['state']),
            new DateTimeImmutable($row['created_at']),
        );
    }
}
