<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

/**
 * @psalm-type InvoiceArray = array{
 *   id?: int|string,
 *   number?: string|int|null,
 *   currency?: string|null,
 *   total?: float|int|string|null
 * }
 */
final class Invoice
{
    public function __construct(
        public int $id,
        public ?string $number,
        public ?string $currency,
        public ?float $total
    ) {}

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $id = 0;
        if (array_key_exists('id', $data)) {
            $rawId = $data['id'];
            if (is_int($rawId)) {
                $id = $rawId;
            } elseif (is_string($rawId) && ctype_digit($rawId)) {
                $id = (int) $rawId;
            }
        }

        $number = null;
        if (array_key_exists('number', $data) && (is_string($data['number']) || is_int($data['number']))) {
            $number = (string) $data['number'];
        }

        $currency = null;
        if (array_key_exists('currency', $data) && is_string($data['currency'])) {
            $currency = $data['currency'];
        }

        $total = null;
        if (array_key_exists('total', $data) && (is_float($data['total']) || is_int($data['total']) || is_string($data['total']))) {
            $total = (float) $data['total'];
        }

        return new self($id, $number, $currency, $total);
    }
}
