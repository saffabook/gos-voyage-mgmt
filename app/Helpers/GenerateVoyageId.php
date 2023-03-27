<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class GenerateVoyageId
{
    public static function execute($companyId)
    {
        $referenceCode = self::makeRandomCode(4).'-'.self::makeRandomCode(8).'-'.$companyId;

        return $referenceCode;
    }

    private static function makeRandomCode($total)
    {
        return Str::lower(Str::random($total, '0123456789abcdefghijklmnopqrstuvwxyz'));
    }
}
