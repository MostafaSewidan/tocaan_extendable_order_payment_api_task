<?php
namespace App\DTO\Payment;


class CallBackResponse
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public function __construct(
        public $status,
        public $gatewayTransactionId = null
    )
    {
        $this->validateStatus();
    }

    public static function make($status = null, string $gatewayTransactionId = null): self
    {
        return new self(
            $status ?? self::STATUS_SUCCESS,
            $gatewayTransactionId
        );
    }

    private function validateStatus(): void
    {
        if (!in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FAILED], true)) {
            throw new \InvalidArgumentException("Invalid status: {$this->status}. Allowed values: '".self::STATUS_SUCCESS."', '".self::STATUS_FAILED."'.");
        }
    }
}
