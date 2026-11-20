<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Citizen\Citizen;
use App\Domain\Citizen\CitizenRepository;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Shared\ValueObject\Cpf;
use App\Domain\Shared\ValueObject\FullName;
use DateTimeImmutable;
use PDO;
use PDOException;

final readonly class PdoCitizenRepository implements CitizenRepository
{
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    private const int MYSQL_DUPLICATE_ENTRY = 1062;

    public function __construct(private PDO $pdo) {}

    public function add(Citizen $citizen): Citizen
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO citizens (name, cpf, created_at) VALUES (:name, :cpf, :created_at)',
        );

        try {
            $statement->execute([
                'name' => $citizen->name->value(),
                'cpf' => $citizen->cpf->value(),
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
        $statement = $this->pdo->prepare('SELECT id, name, cpf, created_at FROM citizens WHERE cpf = :cpf');
        $statement->execute(['cpf' => $cpf->value()]);

        /** @var array{id: int, name: string, cpf: string, created_at: string}|false $row */
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function searchByName(string $term): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, name, cpf, created_at FROM citizens WHERE name LIKE :pattern ESCAPE '!' ORDER BY name, id",
        );
        $statement->execute(['pattern' => '%' . $this->escapeLike($term) . '%']);

        /** @var list<array{id: int, name: string, cpf: string, created_at: string}> $rows */
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
     * @param array{id: int, name: string, cpf: string, created_at: string} $row
     */
    private function hydrate(array $row): Citizen
    {
        return new Citizen(
            $row['id'],
            FullName::fromString($row['name']),
            Cpf::fromString($row['cpf']),
            new DateTimeImmutable($row['created_at']),
        );
    }
}
