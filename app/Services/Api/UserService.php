<?php

namespace App\Services\Api;

use App\DTO\Api\ServiceResponse;
use App\Http\Resources\Api\UserResource;
use App\Repositories\UserRepository;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserService
{
    public function __construct(private UserRepository $userRepository)
    {
        //
    }

   public function register(array $data): ServiceResponse
   {
        $user = $this->userRepository->create($data);
        
        $token = JWTAuth::fromUser($user);
        
        // Return the created user
        return ServiceResponse::fromArray([
            'data' => $this->userWithToken($token, $user)
        ]);
    }

    public function login($email, $password): ServiceResponse
    {
        if (! $token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {

            return ServiceResponse::fromArray([
                'message' => 'Unauthorized',
                'status' => ServiceResponse::STATUS_ERROR
            ]);
        }

        return ServiceResponse::fromArray([
            'data' => $this->userWithToken($token, JWTAuth::user())
        ]);
    }

    public function logout(): ServiceResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return ServiceResponse::fromArray([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh(): ServiceResponse
    {
        return ServiceResponse::fromArray([
            'data' => $this->userWithToken(JWTAuth::refresh(JWTAuth::getToken()), JWTAuth::user())
        ]);
    }

    private function userWithToken($token,$user)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => UserResource::make($user)
        ];
    }
}
