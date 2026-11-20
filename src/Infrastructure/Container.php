<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\SearchCitizens\SearchCitizens;
use App\Infrastructure\Config\Config;
use App\Infrastructure\Http\Session;
use App\Infrastructure\Persistence\Connection;
use App\Infrastructure\Persistence\PdoCitizenRepository;
use App\Infrastructure\View\ViewRenderer;
use App\Presentation\Controller\CitizenController;
use App\Presentation\Kernel;

/**
 * Manual dependency wiring: every object receives its collaborators
 * through the constructor, and this is the only place that creates them.
 */
final readonly class Container
{
    public function __construct(private string $rootPath) {}

    public function kernel(): Kernel
    {
        $view = new ViewRenderer($this->rootPath . '/templates', $this->rootPath . '/public');

        return new Kernel(fn(): CitizenController => $this->citizenController($view), $view);
    }

    private function citizenController(ViewRenderer $view): CitizenController
    {
        $config = Config::fromEnvironment();
        $citizens = new PdoCitizenRepository(Connection::create($config, $config->dbName));

        return new CitizenController(
            new RegisterCitizen($citizens),
            new SearchCitizens($citizens),
            $view,
            Session::start(),
        );
    }
}
