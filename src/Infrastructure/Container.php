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

/**
 * Manual dependency wiring: every object receives its collaborators
 * through the constructor, and this is the only place that creates them.
 */
final class Container
{
    private ?ViewRenderer $view = null;

    private ?Session $session = null;

    public function __construct(private readonly string $rootPath) {}

    public function citizenController(): CitizenController
    {
        $config = Config::fromEnvironment();
        $citizens = new PdoCitizenRepository(Connection::create($config, $config->dbName));

        return new CitizenController(
            new RegisterCitizen($citizens),
            new SearchCitizens($citizens),
            $this->view(),
            $this->session(),
        );
    }

    public function view(): ViewRenderer
    {
        return $this->view ??= new ViewRenderer($this->rootPath . '/templates');
    }

    private function session(): Session
    {
        return $this->session ??= Session::start();
    }
}
