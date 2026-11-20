<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final readonly class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {}

    /**
     * 303 See Other: after a POST, the browser follows the redirect with a GET.
     */
    public static function redirect(string $location): self
    {
        return new self('', 303, ['Location' => $location]);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body;
    }
}
