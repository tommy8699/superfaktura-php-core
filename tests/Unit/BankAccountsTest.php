<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use Tommy8699\SuperFaktura\Core\Config;
use Tommy8699\SuperFaktura\Core\SuperFakturaClient;

final class BankAccountsTest extends TestCase
{
    private function mockClient(array $responses): SuperFakturaClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack, 'base_uri' => 'https://sandbox.superfaktura.sk/']);
        $cfg = new Config('email@example.com', 'key', '1', sandbox: true);
        return new SuperFakturaClient($cfg, $client, new NullLogger());
    }

    public function test_add_bank_account(): void
    {
        $respBody = json_encode([
            'BankAccount' => [
                'id' => '2',
                'bank_name' => 'NovaBanka',
                'iban' => 'SK000011112222333344',
                'swift' => 'SUZUKI',
                'default' => 1,
                'show' => 1
            ],
            'error' => 0
        ]);
        $sf = $this->mockClient([new Response(200, [], $respBody)]);
        $dto = $sf->addBankAccount(['bank_name' => 'NovaBanka', 'iban' => 'X']);
        $this->assertSame(2, $dto->id);
        $this->assertSame('NovaBanka', $dto->bankName);
    }

    public function test_update_bank_account(): void
    {
        $respBody = json_encode([
            'error' => '0',
            'message' => [
                'BankAccount' => [
                    'id' => 1,
                    'bank_name' => 'StaroNovaBanka',
                    'swift' => '77777',
                    'default' => true,
                    'iban' => 'SK0123',
                    'show' => true
                ]
            ]
        ]);
        $sf = $this->mockClient([new Response(200, [], $respBody)]);
        $dto = $sf->updateBankAccount(1, ['bank_name' => 'StaroNovaBanka']);
        $this->assertSame(1, $dto->id);
        $this->assertSame('StaroNovaBanka', $dto->bankName);
        $this->assertTrue($dto->default);
    }

    public function test_delete_bank_account(): void
    {
        $respBody = json_encode(['error' => '0', 'message' => 'Bankový účet zmazaný']);
        $sf = $this->mockClient([new Response(200, [], $respBody)]);
        $ok = $sf->deleteBankAccount(1);
        $this->assertTrue($ok);
    }

    public function test_list_bank_accounts(): void
    {
        $respBody = json_encode([
            'BankAccounts' => [
                ['BankAccount' => ['id' => '1', 'bank_name' => 'FatraBanka', 'iban' => 'SK0123', 'default' => true, 'show' => true]],
                ['BankAccount' => ['id' => '2', 'bank_name' => 'NovaBanka',  'iban' => 'SK0456', 'default' => 0,    'show' => 1]]
            ],
            'error' => 0
        ]);
        $sf = $this->mockClient([new Response(200, [], $respBody)]);
        $list = $sf->listBankAccounts();
        $this->assertCount(2, $list);
        $this->assertSame('FatraBanka', $list[0]->bankName);
        $this->assertSame(2, $list[1]->id);
        $this->assertFalse($list[1]->default);
    }
}
