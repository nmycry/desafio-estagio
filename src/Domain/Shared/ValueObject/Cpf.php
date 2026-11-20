<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use App\Domain\Exception\InvalidCpfException;

final readonly class Cpf
{
    private const int LENGTH = 11;

    private function __construct(private string $digits) {}

    /**
     * Accepts the CPF with or without mask (000.000.000-00).
     *
     * @throws InvalidCpfException
     */
    public static function fromString(string $cpf): self
    {
        $digits = self::onlyDigits($cpf);

        if (!self::isValid($digits)) {
            throw InvalidCpfException::invalid();
        }

        return new self($digits);
    }

    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public function value(): string
    {
        return $this->digits;
    }

    public function formatted(): string
    {
        return sprintf(
            '%s.%s.%s-%s',
            substr($this->digits, 0, 3),
            substr($this->digits, 3, 3),
            substr($this->digits, 6, 3),
            substr($this->digits, 9, 2),
        );
    }

    private static function isValid(string $digits): bool
    {
        if (strlen($digits) !== self::LENGTH || self::isRepeatedSequence($digits)) {
            return false;
        }

        return self::checkDigit(substr($digits, 0, 9)) === (int) $digits[9]
            && self::checkDigit(substr($digits, 0, 10)) === (int) $digits[10];
    }

    private static function isRepeatedSequence(string $digits): bool
    {
        return count_chars($digits, 3) === $digits[0];
    }

    /**
     * Each digit is weighted from (length + 1) down to 2; the check digit is
     * 11 minus the remainder of the sum by 11, or 0 when that result is 10 or 11.
     */
    private static function checkDigit(string $base): int
    {
        $weight = strlen($base) + 1;
        $sum = 0;

        foreach (str_split($base) as $digit) {
            $sum += (int) $digit * $weight--;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
