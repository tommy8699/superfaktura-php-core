<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Dto;

/**
 * @phpstan-type Assoc array<string,mixed>
 */
final class BankAccount
{
    public function __construct(
        public int $id,
        public ?string $bankName,
        public ?string $iban,
        public ?string $swift,
        public bool $default,
        public bool $show,
        public ?string $account,
        public ?string $bankCode,
        public ?string $currency
    ) {
    }

    /**
     * @param Assoc $data Accepts root array or {"BankAccount": {...}}
     */
    public static function fromArray(array $data): self
    {
        if (array_key_exists('BankAccount', $data) && is_array($data['BankAccount'])) {
            /** @var Assoc $data */
            $data = $data['BankAccount'];
        }

        $id = 0;
        if (array_key_exists('id', $data)) {
            $raw = $data['id'];
            if (is_int($raw)) {
                $id = $raw;
            } elseif (is_string($raw) && ctype_digit($raw)) {
                $id = (int) $raw;
            }
        }

        $bankName = array_key_exists('bank_name', $data) && is_string($data['bank_name']) ? $data['bank_name'] : null;
        $iban     = array_key_exists('iban', $data) && is_string($data['iban']) ? $data['iban'] : null;
        $swift    = array_key_exists('swift', $data) && is_string($data['swift']) ? $data['swift'] : null;

        $default = false;
        if (array_key_exists('default', $data)) {
            $v = $data['default'];
            if (is_bool($v)) {
                $default = $v;
            } elseif (is_int($v)) {
                $default = $v == 1;
            } elseif (is_string($v)) {
                $default = ($v == '1' or strtolower($v) == 'true');
            }
        }

        $show = false;
        if (array_key_exists('show', $data)) {
            $v = $data['show'];
            if (is_bool($v)) {
                $show = $v;
            } elseif (is_int($v)) {
                $show = $v == 1;
            } elseif (is_string($v)) {
                $show = ($v == '1' or strtolower($v) == 'true');
            }
        }

        $account  = array_key_exists('account', $data) && is_string($data['account']) ? $data['account'] : null;
        $bankCode = array_key_exists('bank_code', $data) && is_string($data['bank_code']) ? $data['bank_code'] : null;
        $currency = array_key_exists('currency', $data) && is_string($data['currency']) ? $data['currency'] : null;

        return new self($id, $bankName, $iban, $swift, $default, $show, $account, $bankCode, $currency);
    }
}
