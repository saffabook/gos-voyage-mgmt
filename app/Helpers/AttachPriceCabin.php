<?php

namespace App\Helpers;

use App\Models\VoyagePrice;

class AttachPriceCabin
{
    /**
     * Undocumented function
     *
     * @param int $priceId
     * @param int $cabinId
     *
     */
    public static function execute($priceId, $cabinId)
    {
        $price = VoyagePrice::find($priceId);

        return $price->cabins()->attach($cabinId);
    }
}
