<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Closure;

final class Router
{
    /** @var array<string, array<string, Closure(Request): Response>> handlers by path and method */
    private array $routes = [];

    /**
     * @param Closure(Request): Response $handler
     */
    public function get(string $path, Closure $handler): void
    {
        $this->routes[$path]['GET'] = $handler;
    }

    /**
     * @param Closure(Request): Response $handler
     */
    public function post(string $path, Closure $handler): void
    {
        $this->routes[$path]['POST'] = $handler;
    }

    /**
     * @throws HttpException when no route matches the path or the method
     */
    public function dispatch(Request $request): Response
    {
        $handlers = $this->routes[$request->path] ?? throw HttpException::notFound();
        $handler = $handlers[$request->method] ?? throw HttpException::methodNotAllowed();

        return $handler($request);
    }
}
