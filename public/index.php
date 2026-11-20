<?php

declare(strict_types=1);

use App\Infrastructure\Container;
use App\Infrastructure\Http\HttpException;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container(dirname(__DIR__));

$errorPage = static fn(int $status, string $message): Response => new Response(
    $container->view()->render('error', ['title' => 'Erro ' . $status, 'message' => $message]),
    $status,
);

try {
    $controller = $container->citizenController();

    $router = new Router();
    $router->get('/', $controller->registerForm(...));
    $router->post('/cidadaos', $controller->register(...));
    $router->get('/pesquisa', $controller->search(...));

    $response = $router->dispatch(Request::fromGlobals());
} catch (HttpException $exception) {
    $response = $errorPage($exception->status, $exception->getMessage());
} catch (Throwable $exception) {
    error_log((string) $exception);
    $response = $errorPage(500, 'Ocorreu um erro inesperado. Tente novamente em instantes.');
}

$response->send();
