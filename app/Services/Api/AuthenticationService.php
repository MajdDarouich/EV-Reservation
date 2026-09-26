<?php

namespace App\Services\Api;

use App\Enums\UserStatus;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AuthenticationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected OtpService $otpService)
    {
        //
    }

    public function Register(array $data)
    {
        DB::beginTransaction();

        try {
            $role = 'ev user';

            $user = User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'password' => $data['password'],
            ]);

            $user->assignRole($role);

            $this->otpService->generateAndSend($user);

            DB::commit();

            return [
                'user' => $user,
                'status' => 201,
                'message' => 'User registered successfully.',
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function VerifyOtp(VerifyOtpRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::where('phone_number', $request->phone_number)->firstOrFail();

            if ($user->hasVerifiedPhone()) {
                DB::commit();

                return [
                    'message' => 'Phone number already verified.',
                    'status' => 409,
                ];
            }

            $latestOtp = $this->otpService->latestUnverified($user);

            if ($latestOtp && $latestOtp->attempts >= 5) {
                DB::commit();

                return [
                    'message' => 'Too many attempts. Request a new OTP.',
                    'status' => 429,
                ];
            }

            if (! $this->otpService->verify($user, $request->otp)) {
                DB::commit();

                return [
                    'message' => 'Invalid or expired OTP.',
                    'status' => 422,
                ];
            }

            $token = $user->createToken('auth_token')->accessToken;

            DB::commit();

            return [
                'message' => 'Phone verified successfully.',
                'token' => $token,
                'user' => $user,
                'status' => 200,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::where('phone_number', $request->phone_number)->firstOrFail();

            if ($user->hasVerifiedPhone()) {
                DB::commit();

                return [
                    'message' => 'Phone already verified.',
                    'status' => 409,
                ];
            }

            $latestOtp = $this->otpService->latestUnverified($user);

            if ($latestOtp && now()->lt($latestOtp->created_at->addMinute())) {
                DB::commit();

                return [
                    'message' => 'Please wait before requesting a new code.',
                    'status' => 429,
                ];
            }

            $this->otpService->generateAndSend($user);

            DB::commit();

            return [
                'message' => 'A new OTP has been sent to your phone.',
                'status' => 200,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function Login(array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::where('phone_number', $data['phone_number'])->first();

            if (! $user) {
                DB::commit();

                return [
                    'message' => 'User not found.',
                    'status' => 404,
                ];
            }

            if (! Hash::check($data['password'], $user->password)) {
                DB::commit();

                return [
                    'message' => 'Invalid credentials.',
                    'status' => 401,
                ];
            }

            if ($user->status === UserStatus::SUSPENDED) {
                DB::commit();

                return [
                    'message' => 'Your account is inactive. Please contact support.',
                    'status' => 403,
                ];
            }

            if (! $user->hasVerifiedPhone()) {
                DB::commit();

                return [
                    'message' => 'Phone number not verified. Please verify your phone number first.',
                    'status' => 403,
                ];
            }

            $token = $user->createToken('auth_token')->accessToken;

            DB::commit();

            return [
                'message' => 'Login successful.',
                'token' => $token,
                'user' => $user,
                'status' => 200,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function Logout()
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            if ($user) {
                $user->token()->revoke();
            }

            DB::commit();

            return [
                'message' => 'Logged out successfully.',
                'status' => 200,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
