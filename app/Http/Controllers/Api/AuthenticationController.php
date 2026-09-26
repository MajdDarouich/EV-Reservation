<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginApiRequest;
use App\Http\Requests\Api\RegisterApiRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\Api\AuthenticationService;

class AuthenticationController extends Controller
{
    public function __construct(protected AuthenticationService $service)
    {
        //
    }

    public function Register(RegisterApiRequest $request)
    {
        $data = $request->validated();
        $response = $this->service->Register($data);

        return response()->json([
            'user' => new UserResource($response['user']),
            'message' => $response['message'],
        ], $response['status']);
    }

    public function VerifyOtp(VerifyOtpRequest $request)
    {
        $response = $this->service->VerifyOtp($request);

        return response()->json([
            'message' => $response['message'],
            ...isset($response['token']) ? ['token' => $response['token']] : [],
            ...isset($response['user']) ? ['user' => new UserResource($response['user'])] : [],
        ], $response['status']);
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $response = $this->service->resendOtp($request);

        return response()->json([
            'message' => $response['message'],
        ], $response['status']);
    }

    public function Login(LoginApiRequest $request)
    {
        $data = $request->validated();
        $response = $this->service->Login($data);

        return response()->json([
            'message' => $response['message'],
            ...isset($response['token']) ? ['token' => $response['token']] : [],
            ...isset($response['user']) ? ['user' => new UserResource($response['user'])] : [],
        ], $response['status']);
    }

    public function Logout()
    {
        $response = $this->service->Logout();

        return response()->json([
            'message' => $response['message'],
        ], $response['status']);
    }
}
