<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

/**
 * @phpstan-type Assoc array<string,mixed>
 */
final class PaymentReceipt
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public string $currency,
        public string $created
    ) {
    }

    /**
     * @param Assoc $data
     */
    public static function fromArray(array $data): self
    {
        $ip = [];
        if (array_key_exists('InvoicePayment', $data) && is_array($data['InvoicePayment'])) {
            /** @var Assoc $ip */
            $ip = $data['InvoicePayment'];
        }

        $invoiceId = 0;
        if (array_key_exists('invoice_id', $ip)) {
            $raw = $ip['invoice_id'];
            if (is_int($raw)) {
                $invoiceId = $raw;
            } elseif (is_string($raw) && ctype_digit($raw)) {
                $invoiceId = (int) $raw;
            }
        }

        $amount = 0.0;
        if (array_key_exists('amount', $ip)) {
            $rawAmt = $ip['amount'];
            if (is_float($rawAmt) || is_int($rawAmt) || (is_string($rawAmt) && is_numeric($rawAmt))) {
                $amount = (float) $rawAmt;
            }
        }

        $currency = (array_key_exists('currency', $ip) && is_string($ip['currency'])) ? $ip['currency'] : 'EUR';
        $created  = (array_key_exists('created', $ip) && is_string($ip['created'])) ? $ip['created'] : '';

        return new self($invoiceId, $amount, $currency, $created);
    }
}
