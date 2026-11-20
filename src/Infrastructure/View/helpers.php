<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Public URL of a file under public/, versioned by its modification time so
 * browsers download it again whenever it changes: "/assets/app.css?v=1763647639".
 */
function asset(string $path): string
{
    $file = dirname(__DIR__, 3) . '/public' . $path;

    return $path . '?v=' . (is_file($file) ? filemtime($file) : 0);
}

/**
 * First letter of the first and of the last name: "Maria da Silva" -> "MS".
 */
function initials(string $fullName): string
{
    $names = explode(' ', $fullName);

    return mb_strtoupper(mb_substr($names[0], 0, 1) . mb_substr(end($names), 0, 1));
}
