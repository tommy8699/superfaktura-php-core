<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core;

final class Config
{
    public readonly string $apiEmail;
    public readonly string $apiKey;
    public readonly string $companyId;
    public readonly bool $sandbox;
    public readonly string $baseUrl;
    public readonly float $timeout;
    public readonly float $connectTimeout;
    public readonly int $maxRetries;

    public function __construct(
        string $apiEmail,
        string $apiKey,
        string $companyId,
        bool $sandbox = true,
        float $timeout = 15.0,
        float $connectTimeout = 5.0,
        int $maxRetries = 3
    ) {
        $this->apiEmail = $apiEmail;
        $this->apiKey = $apiKey;
        $this->companyId = $companyId;
        $this->sandbox = $sandbox;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->maxRetries = $maxRetries;
        $this->baseUrl = $sandbox ? 'https://sandbox.superfaktura.sk/' : 'https://moja.superfaktura.sk/';
        $this->assertAllowedHost();
    }

    private function assertAllowedHost(): void
    {
        $host = parse_url($this->baseUrl, PHP_URL_HOST) ?: '';
        $allowed = ['sandbox.superfaktura.sk', 'moja.superfaktura.sk'];
        if (!in_array($host, $allowed, true)) {
            throw new \InvalidArgumentException('Base URL host not allowed: ' . $host);
        }
    }

    public function buildAuthHeader(): string
    {
        return sprintf('SFAPI email=%s&apikey=%s&company_id=%s', $this->apiEmail, $this->apiKey, $this->companyId);
    }
}
