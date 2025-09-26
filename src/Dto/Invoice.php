<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

final class Invoice
{
    public function __construct(
        public int $id,
        public ?string $number,
        public ?string $currency,
        public ?float $total
    ) {}

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            number: isset($data['number']) ? (string)$data['number'] : null,
            currency: isset($data['currency']) ? (string)$data['currency'] : null,
            total: isset($data['total']) ? (float)$data['total'] : null,
        );
    }
}
