<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\Api\UserService;
use App\Traits\ResponseHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    use ResponseHandler;

    public function __construct(private UserService $userService)
    {
        //
    }
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        try {

            return $this->serviceResponse(
                $this->userService->register($request->validated())
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * Authenticate a user and return a token.
     */
    public function login(LoginRequest $request)
    {
        try {

            return $this->serviceResponse(
                $this->userService->login(
                    $request->email,
                    $request->password
                )
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * Log the user out.
     */
    public function logout()
    {
        return $this->serviceResponse(
            $this->userService->logout()
        );
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return $this->serviceResponse(
            $this->userService->refresh()
        );
    }
}
