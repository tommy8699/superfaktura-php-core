<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tommy8699\SuperFaktura\Core\Config;
use Tommy8699\SuperFaktura\Core\SuperFakturaClient;

final class LiveSuperFakturaTest extends TestCase
{
    private function creds(): ?array
    {
        $email = getenv('SF_API_EMAIL') ?: null;
        $key   = getenv('SF_API_KEY') ?: null;
        $cid   = getenv('SF_COMPANY_ID') ?: null;
        $sb    = getenv('SF_SANDBOX') ?: '1';

        if (!$email || !$key || !$cid) {
            return null;
        }
        return [
            'email' => $email,
            'key' => $key,
            'cid' => $cid,
            'sandbox' => $sb === '1',
        ];
    }

    protected function setUp(): void
    {
        if (!$this->creds()) {
            $this->markTestSkipped('SF_* env vars not provided; skipping live integration tests.');
        }
    }

    public function test_can_list_bank_accounts_live(): void
    {
        $creds = $this->creds();
        $cfg = new Config($creds['email'], $creds['key'], $creds['cid'], sandbox: $creds['sandbox']);
        $sf  = SuperFakturaClient::createDefault($cfg);
        $list = $sf->listBankAccounts();
        $this->assertIsArray($list);
        // no strict assertions to avoid flakiness
    }
}
