<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

use RuntimeException;

final readonly class Config
{
    private function __construct(
        public string $dbHost,
        public int $dbPort,
        public string $dbName,
        public string $dbTestName,
        public string $dbUser,
        public string $dbPassword,
    ) {}

    public static function fromEnvironment(): self
    {
        return new self(
            self::env('DB_HOST'),
            (int) self::env('DB_PORT'),
            self::env('DB_NAME'),
            self::env('DB_TEST_NAME'),
            self::env('DB_USER'),
            self::env('DB_PASSWORD'),
        );
    }

    private static function env(string $name): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            throw new RuntimeException(sprintf('Missing environment variable "%s".', $name));
        }

        return $value;
    }
}
