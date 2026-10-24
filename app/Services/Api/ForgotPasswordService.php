<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordService
{
    public function __construct(protected OtpService $otpService) {}

    public function sendResetCode(string $phoneNumber): array
    {
        $user = User::where('phone_number', $phoneNumber)->firstOrFail();
        $this->otpService->generateAndSend(
            $user,
            'Your password reset code is: %s. It expires in 5 minutes.'
        );

        return [
            'message' => 'A password reset code has been sent to your phone.',
            'status' => 200,
        ];
    }

    public function resetPassword(array $data): array
    {
        DB::beginTransaction();

        try {
            $user = User::where('phone_number', $data['phone_number'])->firstOrFail();
            $latestOtp = $this->otpService->latestUnverified($user);

            if ($latestOtp && $latestOtp->attempts >= 5) {
                DB::commit();

                return [
                    'message' => 'Too many attempts. Request a new OTP.',
                    'status' => 429,
                ];
            }

            if (! $this->otpService->verifyCode($user, $data['otp'])) {
                DB::commit();

                return [
                    'message' => 'Invalid or expired OTP.',
                    'status' => 422,
                ];
            }

            $user->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
            DB::commit();

            return [
                'message' => 'Password has been reset successfully.',
                'status' => 200,
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
