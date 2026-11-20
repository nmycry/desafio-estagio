<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use RuntimeException;

/**
 * An HTTP error whose status and message can be shown to the user.
 */
final class HttpException extends RuntimeException
{
    private function __construct(public readonly int $status, string $message)
    {
        parent::__construct($message);
    }

    public static function badRequest(string $message): self
    {
        return new self(400, $message);
    }

    public static function notFound(): self
    {
        return new self(404, 'A página que você procura não existe.');
    }

    public static function methodNotAllowed(): self
    {
        return new self(405, 'Método não permitido para este endereço.');
    }
}
