<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class GenerateVoyageId
{
    public static function execute($companyId)
    {
        $randomString = Str::lower(Str::random(10, '0123456789abcdefghijklmnopqrstuvwxyz'));

        $saltRandomString = Str::lower(Str::random(2, '0123456789abcdefghijklmnopqrstuvwxyz'));

        $referenceCode = $saltRandomString.'-'.$randomString.'-'.$companyId;

        return $referenceCode;
    }
}
