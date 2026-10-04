<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    public static function normalize(string $phoneNumber, ?string $defaultCountryCode = null): string
    {
        $phoneNumber = preg_replace('/[^0-9+]/', '', trim($phoneNumber)) ?? trim($phoneNumber);

        if (str_starts_with($phoneNumber, '00')) {
            return '+'.substr($phoneNumber, 2);
        }

        if (str_starts_with($phoneNumber, '0') && $defaultCountryCode !== null) {
            $countryCode = preg_replace('/[^0-9]/', '', $defaultCountryCode) ?? '';

            if ($countryCode !== '') {
                return '+'.$countryCode.substr($phoneNumber, 1);
            }
        }

        return $phoneNumber;
    }
}
