# SuperFaktura PHP Core (Production)

Production-ready, framework-agnostic PHP klient pre **SuperFaktura API**.

## Features
- `declare(strict_types=1)` a **DTOs** (typová bezpečnosť)
- **Retry & backoff** (429/5xx) cez Guzzle middleware (konfigurovateľné)
- **Idempotency-Key** podpora (vytváranie faktúr / platby)
- Voliteľný **PSR-3 Logger** (trace `request_id`, maskované auth hlavičky)
- Konfigurovateľné **timeouts** (`timeout`, `connect_timeout`)
- **PHPStan (max)**, **PHPUnit**, **CS Fixer**, **GitHub Actions** CI

## Inštalácia
```bash
composer require tommy8699/superfaktura-core
```

## Použitie
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Tommy8699\SuperFaktura\Core\Config;
use Tommy8699\SuperFaktura\Core\SuperFakturaClient;

$logger = new Logger('sf');
$logger->pushHandler(new StreamHandler('php://stdout'));

$cfg = new Config(
    apiEmail: 'you@example.com',
    apiKey: 'your_api_key',
    companyId: '12345',
    sandbox: true,
    timeout: 15.0,
    connectTimeout: 5.0,
    maxRetries: 3
);

$client = SuperFakturaClient::createDefault($cfg, $logger);

// Idempotentné vytvorenie faktúry (rovnaký key => bezpečný retry)
$invoice = $client->createInvoice([...], idempotencyKey: null); // null => UUID vygenerovaný

// Download PDF do súboru
$client->downloadInvoice($invoice->id, __DIR__.'/invoice.pdf');
```

## Testy a kvalita
```bash
composer install
composer check   # PHPStan + PHPUnit
composer fix     # CS Fixer
```

## Bezpečnosť
- Logy **nikdy** nevypisujú celé `Authorization` hlavičky (maskované).
- Base URL validované na povolené domény.
- Odporúčame ukladať Idempotency-Key a korelované `request_id` do DB/logov.
