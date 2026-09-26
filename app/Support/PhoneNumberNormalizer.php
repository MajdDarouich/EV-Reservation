<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    public static function normalize(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^0-9+]/', '', trim($phoneNumber)) ?? trim($phoneNumber);

        if (str_starts_with($phoneNumber, '00')) {
            return '+' . substr($phoneNumber, 2);
        }

        if (str_starts_with($phoneNumber, '0')) {
            return '+963' . substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '963')) {
            return '+' . $phoneNumber;
        }

        return $phoneNumber;
    }
}
