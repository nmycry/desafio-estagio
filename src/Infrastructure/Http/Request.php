<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final readonly class Request
{
    /**
     * @param array<array-key, mixed> $query
     * @param array<array-key, mixed> $body
     */
    public function __construct(
        public string $method,
        public string $path,
        private array $query = [],
        private array $body = [],
    ) {}

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = is_string($uri) ? parse_url($uri, PHP_URL_PATH) : null;

        return new self(
            is_string($method) ? strtoupper($method) : 'GET',
            is_string($path) ? $path : '/',
            $_GET,
            $_POST,
        );
    }

    /**
     * Returns null when the parameter is absent, so callers can tell
     * "not submitted" apart from "submitted empty".
     */
    public function query(string $key): ?string
    {
        $value = $this->query[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function input(string $key): string
    {
        $value = $this->body[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
