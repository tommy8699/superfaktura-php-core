<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Support;

final class Mask
{
    public static function auth(string $header): string
    {
        $subject = (string) $header;
        if ($subject === '') {
            return '***';
        }

        $masked = preg_replace('/(apikey=)([^&]+)/i', '$1***', $subject);
        $masked = preg_replace('/(email=)([^&]+)/i', '$1***', $masked ?? $subject);

        return $masked ?? '***';
    }
}
