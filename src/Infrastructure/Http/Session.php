<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final class Session
{
    private const string CSRF_KEY = 'csrf_token';

    private const string FLASH_PREFIX = 'flash.';

    private function __construct() {}

    public static function start(): self
    {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);

        return new self();
    }

    /**
     * Stores data that survives only until the next read (Post/Redirect/Get).
     *
     * @param array<string, string> $data
     */
    public function flash(string $key, array $data): void
    {
        $_SESSION[self::FLASH_PREFIX . $key] = $data;
    }

    /**
     * @return array<string, string>|null
     */
    public function pullFlash(string $key): ?array
    {
        /** @var array<string, string>|null $data */
        $data = $_SESSION[self::FLASH_PREFIX . $key] ?? null;
        unset($_SESSION[self::FLASH_PREFIX . $key]);

        return $data;
    }

    public function csrfToken(): string
    {
        $token = $_SESSION[self::CSRF_KEY] ?? null;

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::CSRF_KEY] = $token;
        }

        return $token;
    }

    public function isValidCsrfToken(string $token): bool
    {
        return hash_equals($this->csrfToken(), $token);
    }
}
