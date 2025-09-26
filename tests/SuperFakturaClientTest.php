<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use Tommy8699\SuperFaktura\Core\Config;
use Tommy8699\SuperFaktura\Core\SuperFakturaClient;

final class SuperFakturaClientTest extends TestCase
{
    private function mockClient(array $responses): SuperFakturaClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'base_uri' => 'https://sandbox.superfaktura.sk/']);
        $cfg = new Config('email@example.com', 'key', '1', sandbox: true);
        return new SuperFakturaClient($cfg, $client, new NullLogger());
    }

    public function test_create_invoice_ok(): void
    {
        $sf = $this->mockClient([new Response(200, [], json_encode(['id' => 123, 'number' => '2025-001', 'currency' => 'EUR', 'total' => 100.0]))]);
        $invoice = $sf->createInvoice(['Invoice' => ['name' => 'Test']]);
        $this->assertSame(123, $invoice->id);
        $this->assertSame('EUR', $invoice->currency);
    }

    public function test_download_invoice_pdf_raw(): void
    {
        $pdfBinary = '%PDF-1.4 mock%';
        $sf = $this->mockClient([new Response(200, ['Content-Type' => 'application/pdf'], $pdfBinary)]);
        $raw = $sf->downloadInvoice(123, null);
        $this->assertNotNull($raw);
        $this->assertStringStartsWith('%PDF', $raw);
    }
}
