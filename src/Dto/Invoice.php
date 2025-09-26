<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

/**
 * @phpstan-type Assoc array<string,mixed>
 */
final class Invoice
{
    public function __construct(
        public int $id,
        public ?string $number,
        public ?string $currency,
        public ?float $total
    ) {
    }

    /**
     * @param Assoc $data
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
        if (array_key_exists('number', $data)) {
            $rawNum = $data['number'];
            if (is_string($rawNum) || is_int($rawNum)) {
                $number = (string) $rawNum;
            }
        }

        $currency = (array_key_exists('currency', $data) && is_string($data['currency'])) ? $data['currency'] : null;

        $total = null;
        if (array_key_exists('total', $data)) {
            $rawTotal = $data['total'];
            if (is_float($rawTotal) || is_int($rawTotal) || (is_string($rawTotal) && is_numeric($rawTotal))) {
                $total = (float) $rawTotal;
            }
        }

        return new self($id, $number, $currency, $total);
    }
}
