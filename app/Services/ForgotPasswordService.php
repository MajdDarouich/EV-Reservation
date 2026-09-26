<?php

namespace App\Services;

use Illuminate\Support\Facades\Password;

class ForgotPasswordService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function sendResetLink(array $data)
    {
        $status = Password::sendResetLink(
            ['email' => $data['email']]
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
