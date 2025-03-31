<?php

namespace App\Traits;

use App\DTO\Api\ServiceResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResponseHandler
{
    public function serviceResponse($response)
    {
        if ($response->status == ServiceResponse::STATUS_ERROR)
            return $this->error($response->message);

        return $this->success($response->data);
    }

    public function paginateResponse($response, $message = 'Success')
    {
        return $response->data->additional([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function created($data, $message = 'Created', $code = 201)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function error($message, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    public function validationError($errors, $message = 'Validation Error', $code = 422)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
    
    public function internalServerError(HttpException $e)
    {
        $code = $e->getStatusCode();
        $exceptionMessage = $e->getMessage();

        match ($code) {
            400 => $message = $exceptionMessage,
            404 => $message = 'Not Found',
            500 => $message = 'Internal Server Error',
        };
        
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    public function unauthorized($message = 'Unauthorized', $code = 401)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
