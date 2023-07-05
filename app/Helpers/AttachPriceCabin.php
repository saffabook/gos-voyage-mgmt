<?php

namespace App\Helpers;

use App\Models\VoyagePrice;

class AttachPriceCabin
{
    /**
     * Function to attach a cabin to a price
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
