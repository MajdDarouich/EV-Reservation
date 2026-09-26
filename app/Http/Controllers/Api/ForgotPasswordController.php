<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordApiRequest;
use App\Http\Requests\Api\ResetPasswordApiRequest;
use App\Services\Api\ForgotPasswordService;

class ForgotPasswordController extends Controller
{
    public function __construct(protected ForgotPasswordService $service) {}

    public function sendResetLink(ForgotPasswordApiRequest $request)
    {
        $response = $this->service->sendResetLink($request->validated('email'));

        return response()->json([
            'message' => $response['message'],
        ], $response['status']);
    }

    public function resetPassword(ResetPasswordApiRequest $request)
    {
        $response = $this->service->resetPassword($request->validated());

        return response()->json([
            'message' => $response['message'],
        ], $response['status']);
    }
}
