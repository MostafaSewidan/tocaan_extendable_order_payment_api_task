<?php
namespace App\DTO\Payment;


class CheckoutResponse
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    public function __construct(
        public $status = self::STATUS_SUCCESS,
        public string $checkoutUrl,
        public string $gatewayTransactionId
    )
    {
        //
    }

    public static function make($status = self::STATUS_SUCCESS,$checkoutUrl = null,$gatewayTransactionId = null): self
    {
        return new self(
            $status, 
            $checkoutUrl,
            $gatewayTransactionId
        );
    }
}
