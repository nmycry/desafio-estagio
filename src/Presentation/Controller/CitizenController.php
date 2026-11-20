<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\UseCase\ListCitizens\ListCitizens;
use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\RegisterCitizen\RegisterCitizenInput;
use App\Application\UseCase\SearchCitizens\EmptySearchTermException;
use App\Application\UseCase\SearchCitizens\SearchCitizens;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\InvalidCpfException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Exception\InvalidStateException;
use App\Infrastructure\Http\HttpException;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Session;
use App\Infrastructure\View\ViewRenderer;

final readonly class CitizenController
{
    private const string REGISTERED_FLASH = 'citizen_registered';

    public function __construct(
        private RegisterCitizen $registerCitizen,
        private SearchCitizens $searchCitizens,
        private ListCitizens $listCitizens,
        private ViewRenderer $view,
        private Session $session,
    ) {}

    public function registerForm(Request $request): Response
    {
        return $this->renderRegisterForm([
            'registered' => $this->session->pullFlash(self::REGISTERED_FLASH),
        ]);
    }

    public function register(Request $request): Response
    {
        if (!$this->session->isValidCsrfToken($request->input('_token'))) {
            throw HttpException::badRequest('Sua sessão expirou. Recarregue a página e tente novamente.');
        }

        $input = new RegisterCitizenInput(
            $request->input('name'),
            $request->input('cpf'),
            $request->input('state'),
        );

        try {
            $citizen = $this->registerCitizen->handle($input);
        } catch (DomainException $exception) {
            return $this->renderRegisterForm([
                'errors' => [$this->fieldOf($exception) => $exception->getMessage()],
                'old' => $input,
            ], 422);
        }

        $this->session->flash(self::REGISTERED_FLASH, [
            'name' => $citizen->name->value(),
            'cpf' => $citizen->cpf->formatted(),
            'state' => $citizen->state->value,
        ]);

        return Response::redirect('/');
    }

    /**
     * Without a term, lists every registered citizen page by page; with one
     * (even empty, which is a validation error), searches.
     */
    public function search(Request $request): Response
    {
        $term = $request->query('termo');
        $listing = null;
        $citizens = null;
        $error = null;

        if ($term === null) {
            $listing = $this->listCitizens->handle((int) ($request->query('pagina') ?? 1));
        } else {
            try {
                $citizens = $this->searchCitizens->handle($term);
            } catch (EmptySearchTermException $exception) {
                $error = $exception->getMessage();
            }
        }

        return new Response($this->view->render('citizens/search', [
            'title' => 'Pesquisar cidadão',
            'activeTab' => 'search',
            'term' => $term ?? '',
            'listing' => $listing,
            'citizens' => $citizens,
            'error' => $error,
        ]));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderRegisterForm(array $data, int $status = 200): Response
    {
        return new Response($this->view->render('citizens/register', [
            'title' => 'Cadastrar cidadão',
            'activeTab' => 'register',
            'csrfToken' => $this->session->csrfToken(),
            'registered' => null,
            'errors' => [],
            'old' => new RegisterCitizenInput('', '', ''),
            ...$data,
        ]), $status);
    }

    /**
     * The form field whose input caused the error, so the message is shown next to it.
     */
    private function fieldOf(DomainException $exception): string
    {
        return match (true) {
            $exception instanceof InvalidNameException => 'name',
            $exception instanceof InvalidCpfException,
            $exception instanceof CitizenAlreadyExistsException => 'cpf',
            $exception instanceof InvalidStateException => 'state',
            default => throw $exception,
        };
    }
}
