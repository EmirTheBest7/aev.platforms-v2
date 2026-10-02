<?php

declare(strict_types=1);

namespace App\Validation;

/** Input normalisation shared by all validators. Output is plain text; escaping happens at render time. */
final class Text
{
    /** Single-line field: strips control/format characters, collapses whitespace. */
    public static function line(string $value): string
    {
        $value = (string) preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $value);

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    /** Multi-line field: keeps newlines, strips other control characters. */
    public static function multiline(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = (string) preg_replace('/[^\P{Cc}\n]+|\p{Cf}+/u', '', $value);

        return trim((string) preg_replace("/\n{3,}/", "\n\n", $value));
    }
}
