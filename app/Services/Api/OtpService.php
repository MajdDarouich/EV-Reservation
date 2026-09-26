<?php

namespace App\Services\Api;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function generateAndSend(User $user): OtpCode
    {
        $user->otpCodes()
            ->whereNull('verified_at')
            ->delete();

        $otp = $user->otpCodes()->create([
            'code' => (string) random_int(100000, 999999),
            'expires_at' => now()->addMinutes(5),
        ]);

        if (! $this->sendViaUltraMsg(
            $user->phone_number,
            "Your verification code is: {$otp->code}. It expires in 5 minutes."
        )) {
            $otp->delete();

            throw new \RuntimeException('Unable to send the verification code.');
        }

        return $otp;
    }

    public function verify(User $user, string $code): bool
    {
        /** @var OtpCode|null $otp */
        $otp = $user->otpCodes()
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        if (! hash_equals($otp->code, $code)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['verified_at' => now()]);

        $user->forceFill(['phone_verified_at' => now()])->save();

        return true;
    }

    public function latestUnverified(User $user): ?OtpCode
    {
        return $user->otpCodes()
            ->whereNull('verified_at')
            ->latest()
            ->first();
    }

    protected function sendViaUltraMsg(string $phone, string $message): bool
    {
        $instanceId = config('services.ultramsg.instance_id');
        $token = config('services.ultramsg.token');

        $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
            'token' => $token,
            'to' => $phone,
            'body' => $message,
        ]);

        if ($response->failed()) {
            Log::error('UltraMsg OTP send failed', [
                'phone' => $phone,
                'response' => $response->body(),
            ]);

            return false;
        }

        return true;
    }
}
