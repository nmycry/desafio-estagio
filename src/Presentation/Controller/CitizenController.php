<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\RegisterCitizen\RegisterCitizenInput;
use App\Application\UseCase\SearchCitizens\EmptySearchTermException;
use App\Application\UseCase\SearchCitizens\SearchCitizens;
use App\Domain\Exception\DomainException;
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

        $input = new RegisterCitizenInput($request->input('name'), $request->input('cpf'));

        try {
            $citizen = $this->registerCitizen->handle($input);
        } catch (DomainException $exception) {
            return $this->renderRegisterForm(['error' => $exception->getMessage(), 'old' => $input], 422);
        }

        $this->session->flash(self::REGISTERED_FLASH, [
            'message' => 'Cidadão cadastrado com sucesso!',
            'name' => $citizen->name->value(),
            'cpf' => $citizen->cpf->formatted(),
        ]);

        return Response::redirect('/');
    }

    public function search(Request $request): Response
    {
        $term = $request->query('termo');
        $citizens = null;
        $error = null;

        if ($term !== null) {
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
            'error' => null,
            'old' => new RegisterCitizenInput('', ''),
            ...$data,
        ]), $status);
    }
}
