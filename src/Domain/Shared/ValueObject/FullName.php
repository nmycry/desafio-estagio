<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use App\Domain\Exception\InvalidNameException;

final readonly class FullName
{
    public const int MAX_LENGTH = 150;

    private function __construct(private string $value) {}

    /**
     * @throws InvalidNameException
     */
    public static function fromString(string $name): self
    {
        $normalized = $name
            |> trim(...)
            |> (static fn(string $value): string => preg_replace('/\s+/u', ' ', $value) ?? $value);

        if (mb_strlen($normalized) > self::MAX_LENGTH) {
            throw InvalidNameException::tooLong(self::MAX_LENGTH);
        }

        if (preg_match("/^[\p{L}' -]*$/u", $normalized) !== 1) {
            throw InvalidNameException::invalidCharacters();
        }

        if (preg_match("/^\p{L}[\p{L}'-]*( \p{L}[\p{L}'-]*)+$/u", $normalized) !== 1) {
            throw InvalidNameException::missingSurname();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * First letter of the first and of the last name: "Maria da Silva" -> "MS".
     */
    public function initials(): string
    {
        $names = explode(' ', $this->value);

        return mb_strtoupper(mb_substr($names[0], 0, 1) . mb_substr(end($names), 0, 1));
    }
}
