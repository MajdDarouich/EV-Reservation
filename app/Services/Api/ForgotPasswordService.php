<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordService
{
    public function sendResetLink(string $email): array
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return [
                'message' => __($status),
                'status' => 200,
            ];
        }

        return [
            'message' => __($status),
            'status' => 422,
        ];
    }

    public function resetPassword(array $data): array
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return [
                'message' => __($status),
                'status' => 200,
            ];
        }

        return [
            'message' => __($status),
            'status' => 422,
        ];
    }
}
