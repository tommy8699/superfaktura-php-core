<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tommy8699\SuperFaktura\Core\Dto\Invoice;
use Tommy8699\SuperFaktura\Core\Dto\PaymentReceipt;
use Tommy8699\SuperFaktura\Core\Exceptions\ApiException;
use Tommy8699\SuperFaktura\Core\Exceptions\HttpException;
use Tommy8699\SuperFaktura\Core\Support\Mask;

final class SuperFakturaClient
{
    private Client $client;
    private Config $config;
    private ?LoggerInterface $logger;

    public function __construct(Config $config, ?Client $client = null, ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client ?? self::makeClient($config, $logger);
    }

    public static function createDefault(Config $config, ?LoggerInterface $logger = null): self
    {
        return new self($config, null, $logger);
    }

    private static function makeClient(Config $cfg, ?LoggerInterface $logger): Client
    {
        $stack = HandlerStack::create();

        // Retry middleware for 429/5xx
        $stack->push(Middleware::retry(
            function (int $retries, $request, $response, $exception) use ($cfg, $logger): bool {
                if ($retries >= $cfg->maxRetries) {
                    return false;
                }
                if ($response) {
                    $code = $response->getStatusCode();
                    if (in_array($code, [429, 500, 502, 503, 504], true)) {
                        if ($logger) $logger->warning('Retrying request', ['attempt' => $retries + 1, 'status' => $code]);
                        return True;
                    }
                }
                return false;
            },
            function (int $retries): int {
                // exponential backoff in milliseconds
                return (int) (1000 * (2 ** $retries));
            }
        ));

        return new Client([
            'base_uri' => $cfg->baseUrl,
            'timeout' => $cfg->timeout,
            'connect_timeout' => $cfg->connectTimeout,
            'handler' => $stack,
        ]);
    }

    /**
     * @param array<string,mixed> $invoiceData
     */
    public function createInvoice(array $invoiceData, ?string $idempotencyKey = null): Invoice
    {
        $idempotencyKey = $idempotencyKey ?: Uuid::uuid4()->toString();
        $auth = $this->config->buildAuthHeader();
        try {
            $resp = $this->client->post('invoices/create', [
                'headers' => [
                    'Authorization' => $auth,
                    'Idempotency-Key' => $idempotencyKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $invoiceData,
            ]);
            $code = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            if ($this->logger) {
                $this->logger->info('SF createInvoice', [
                    'status' => $code,
                    'idempotency_key' => $idempotencyKey,
                    'auth' => Mask::auth($auth),
                ]);
            }
            if ($code !== 200) {
                throw new HttpException('Failed to create invoice: ' . $body, $code);
            }
            $data = json_decode($body, true);
            $arr = is_array($data) ? $data : ['id' => 0];
            return Invoice::fromArray($arr);
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP error: ' . $e->getMessage(), (int) $e->getCode());
        }
    }

    public function markInvoiceAsPaid(int|string $invoiceId, float $amount, string $currency = 'EUR', ?string $paymentType = 'transfer', ?\DateTimeInterface $paidAt = null, ?string $idempotencyKey = null): PaymentReceipt
    {
        $paidAt = $paidAt ?? new \DateTimeImmutable('now');
        $idempotencyKey = $idempotencyKey ?: Uuid::uuid4()->toString();
        $payload = [
            'InvoicePayment' => [
                'invoice_id' => $invoiceId,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'created' => $paidAt->format('Y-m-d'),
                'currency' => $currency,
            ]
        ];
        $auth = $this->config->buildAuthHeader();
        try {
            $resp = $this->client->post('invoice_payments/add/ajax:1/api:1', [
                'headers' => [
                    'Authorization' => $auth,
                    'Idempotency-Key' => $idempotencyKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'data' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ]);
            $code = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            if ($this->logger) {
                $this->logger->info('SF markInvoiceAsPaid', [
                    'status' => $code,
                    'idempotency_key' => $idempotencyKey,
                    'auth' => Mask::auth($auth),
                ]);
            }
            if ($code !== 200) {
                throw new HttpException('Failed to mark as paid: ' . $body, $code);
            }
            $data = json_decode($body, true);
            $arr = is_array($data) ? $data : ['InvoicePayment' => ['invoice_id' => (int)$invoiceId, 'amount' => $amount, 'currency' => $currency, 'created' => $paidAt->format('Y-m-d')]];
            return PaymentReceipt::fromArray($arr);
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP error: ' . $e->getMessage(), (int) $e->getCode());
        }
    }

    public function getInvoiceById(int|string $invoiceId): array
    {
        $auth = $this->config->buildAuthHeader();
        try {
            $resp = $this->client->get("invoices/view/{$invoiceId}.json", [
                'headers' => [
                    'Authorization' => $auth,
                    'Accept' => 'application/json',
                ],
            ]);
            $code = $resp->getStatusCode();
            $body = (string) $resp->getBody();
            if ($this->logger) {
                $this->logger->info('SF getInvoiceById', ['status' => $code, 'auth' => Mask::auth($auth)]);
            }
            if ($code !== 200) {
                throw new HttpException('Failed to get invoice: ' . $body, $code);
            }
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : ['raw' => $body];
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP error: ' . $e->getMessage(), (int) $e->getCode());
        }
    }

    public function downloadInvoice(int|string $invoiceId, ?string $savePath = null): ?string
    {
        $auth = $this->config->buildAuthHeader();
        try {
            $opts = [
                'headers' => [
                    'Authorization' => $auth,
                    'Accept' => 'application/pdf',
                ],
            ];
            if ($savePath) {
                $opts['sink'] = $savePath;
            }
            $resp = $this->client->get("invoices/view/{$invoiceId}.pdf", $opts);
            $code = $resp->getStatusCode();
            if ($this->logger) {
                $this->logger->info('SF downloadInvoice', ['status' => $code, 'auth' => Mask::auth($auth)]);
            }
            if ($code !== 200) {
                $body = (string) $resp->getBody();
                throw new HttpException('Failed to download invoice: ' . $body, $code);
            }
            if ($savePath) {
                return null;
            }
            return (string) $resp->getBody();
        } catch (GuzzleException $e) {
            throw new ApiException('HTTP error: ' . $e->getMessage(), (int) $e->getCode());
        }
    }
}
