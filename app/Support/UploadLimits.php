<?php

namespace App\Support;

/**
 * What the server will actually accept for an upload: PHP's own upload limits can be lower
 * than an app setting, in which case the upload fails before validation runs. Forms show
 * this effective limit so administrators aren't told a larger file will work.
 */
class UploadLimits
{
    public static function effectiveKb(int $appLimitKb): int
    {
        $limits = array_filter([
            $appLimitKb,
            self::toKb((string) ini_get('upload_max_filesize')),
            self::toKb((string) ini_get('post_max_size')),
        ], fn (int $kb) => $kb > 0);

        return min($limits);
    }

    /** PHP's post_max_size in KB — caps the combined size of a multi-file upload. */
    public static function postMaxKb(): int
    {
        return self::toKb((string) ini_get('post_max_size'));
    }

    private static function toKb(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtoupper(substr($value, -1))) {
            'G' => $number * 1024 * 1024,
            'M' => $number * 1024,
            'K' => $number,
            default => (int) ceil($number / 1024),
        };
    }
}
