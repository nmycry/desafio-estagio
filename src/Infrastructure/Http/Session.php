<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

final class Session
{
    private const string CSRF_KEY = 'csrf_token';

    private const string FLASH_PREFIX = 'flash.';

    /** @var array<array-key, mixed> */
    private array $storage;

    /**
     * Works on the given array by reference: $_SESSION in production,
     * a plain array in tests.
     *
     * @param array<array-key, mixed> $storage
     */
    public function __construct(array &$storage)
    {
        $this->storage = &$storage;
    }

    public static function start(): self
    {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);

        return new self($_SESSION);
    }

    /**
     * Stores data that survives only until the next read (Post/Redirect/Get).
     *
     * @param array<string, string> $data
     */
    public function flash(string $key, array $data): void
    {
        $this->storage[self::FLASH_PREFIX . $key] = $data;
    }

    /**
     * @return array<string, string>|null
     */
    public function pullFlash(string $key): ?array
    {
        /** @var array<string, string>|null $data */
        $data = $this->storage[self::FLASH_PREFIX . $key] ?? null;
        unset($this->storage[self::FLASH_PREFIX . $key]);

        return $data;
    }

    public function csrfToken(): string
    {
        $token = $this->storage[self::CSRF_KEY] ?? null;

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->storage[self::CSRF_KEY] = $token;
        }

        return $token;
    }

    public function isValidCsrfToken(string $token): bool
    {
        return hash_equals($this->csrfToken(), $token);
    }
}
