<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

final class PaymentReceipt
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public string $currency,
        public string $created
    ) {}

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        $ip = $data['InvoicePayment'] ?? [];
        return new self(
            invoiceId: (int) ($ip['invoice_id'] ?? 0),
            amount: (float) ($ip['amount'] ?? 0.0),
            currency: (string) ($ip['currency'] ?? 'EUR'),
            created: (string) ($ip['created'] ?? '')
        );
    }
}
