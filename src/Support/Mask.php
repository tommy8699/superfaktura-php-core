<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Support;

final class Mask
{
    public static function auth(string $header): string
    {
        // mask everything after apikey= and email= partially
        $masked = preg_replace('/(apikey=)([^&]+)/i', '$1***', $header);
        $masked = preg_replace('/(email=)([^&]+)/i', '$1***', $masked);
        return $masked ?? '***';
    }
}
