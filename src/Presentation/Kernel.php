<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Infrastructure\Http\HttpException;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Router;
use App\Infrastructure\View\ViewRenderer;
use App\Presentation\Controller\CitizenController;
use Closure;
use Throwable;

/**
 * Maps routes to controller actions and turns any error into an error page.
 */
final readonly class Kernel
{
    private Router $router;

    /**
     * @param Closure(): CitizenController $citizenController built lazily, so a
     *        failure while creating it (e.g. database offline) becomes a 500 page
     */
    public function __construct(Closure $citizenController, private ViewRenderer $view)
    {
        $this->router = new Router();
        $this->router->get('/', fn(Request $request) => $citizenController()->registerForm($request));
        $this->router->post('/cidadaos', fn(Request $request) => $citizenController()->register($request));
        $this->router->get('/pesquisa', fn(Request $request) => $citizenController()->search($request));
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (HttpException $exception) {
            return $this->errorPage($exception->status, $exception->getMessage());
        } catch (Throwable $exception) {
            error_log((string) $exception);

            return $this->errorPage(500, 'Ocorreu um erro inesperado. Tente novamente em instantes.');
        }
    }

    private function errorPage(int $status, string $message): Response
    {
        return new Response(
            $this->view->render('error', ['title' => 'Erro ' . $status, 'message' => $message]),
            $status,
        );
    }
}
