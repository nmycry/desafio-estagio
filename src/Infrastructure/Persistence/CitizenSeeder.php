<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\RegisterCitizen\RegisterCitizenInput;
use App\Domain\Exception\CitizenAlreadyExistsException;

/**
 * Fills the database with sample citizens for development and evaluation.
 *
 * It goes through the RegisterCitizen use case, so every sample is validated
 * like a form submission, and skips CPFs already registered: running it
 * twice changes nothing.
 */
final readonly class CitizenSeeder
{
    /**
     * Shared surnames (Silva, Souza, Oliveira) return several results in the
     * name search; accents, apostrophes and hyphens exercise the validation.
     *
     * @var list<array{string, string, string}> name, CPF and state
     */
    public const array CITIZENS = [
        ['Maria Aparecida Silva', '142.082.305-17', 'SP'],
        ['João Pedro Souza', '692.560.774-18', 'RJ'],
        ['Ana Clara Oliveira', '973.871.898-89', 'MG'],
        ['José Carlos da Silva', '481.818.397-06', 'BA'],
        ['Francisca das Chagas Souza', '436.691.060-81', 'CE'],
        ['Antônio Ferreira Lima', '358.534.300-76', 'PE'],
        ['Juliana Conceição Santos', '184.665.779-20', 'RS'],
        ['Carlos Eduardo Pereira', '970.856.133-94', 'PR'],
        ["Joana D'Ávila Costa", '951.009.484-69', 'SC'],
        ['Ana-Beatriz Rodrigues', '799.745.564-73', 'GO'],
        ['Paulo Henrique Almeida', '447.170.998-42', 'DF'],
        ['Luíza Helena Oliveira', '697.665.997-85', 'ES'],
        ['Marcos Vinícius Silva', '413.680.923-90', 'AM'],
        ['Fernanda Gonçalves Ribeiro', '763.944.928-68', 'PA'],
        ['Raimundo Nonato Souza', '670.464.249-85', 'MA'],
        ['Beatriz Araújo Carvalho', '499.127.047-20', 'PB'],
        ['Lucas Gabriel Martins', '114.094.176-38', 'RN'],
        ['Patrícia Mendes Barbosa', '906.394.759-30', 'AL'],
        ['Rafael Teixeira da Silva', '311.408.751-59', 'SE'],
        ['Camila Fernandes Rocha', '471.169.940-68', 'PI'],
        ['Gabriel Moreira Dias', '619.323.553-16', 'MT'],
        ['Letícia Nogueira Castro', '306.910.489-40', 'MS'],
        ['Thiago Batista Oliveira', '974.639.940-31', 'TO'],
        ['Sebastião Cardoso Freitas', '211.550.963-39', 'RO'],
        ['Débora Cristina Pinto', '913.472.624-15', 'AC'],
    ];

    public function __construct(private RegisterCitizen $registerCitizen) {}

    /**
     * @return array{created: int, skipped: int}
     */
    public function run(): array
    {
        $created = 0;

        foreach (self::CITIZENS as [$name, $cpf, $state]) {
            try {
                $this->registerCitizen->handle(new RegisterCitizenInput($name, $cpf, $state));
                $created++;
            } catch (CitizenAlreadyExistsException) {
                continue;
            }
        }

        return ['created' => $created, 'skipped' => count(self::CITIZENS) - $created];
    }
}
