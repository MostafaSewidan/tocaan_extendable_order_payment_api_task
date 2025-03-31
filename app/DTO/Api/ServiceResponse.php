<?php
namespace App\DTO\Api;

class ServiceResponse
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    public function __construct(
        public $data = [],
        public string $message = '',
        public string $status = self::STATUS_SUCCESS,
        public $code = null
    )
    {
        //
    }

    public static function fromArray(array $response): self
    {
        return new self(
            $response['data'] ?? [], 
            $response['message'] ?? '',
            $response['status'] ?? self::STATUS_SUCCESS,
            $response['code'] ?? null,
        );
    }

    public static function fromError(array $response): self
    {
        return new self(
            $response['data'] ?? [], 
            $response['message'] ?? '',
            $response['status'] ?? self::STATUS_ERROR,
            $response['code'] ?? null,
        );
    }
}
