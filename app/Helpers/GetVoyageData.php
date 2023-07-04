<?php

namespace App\Helpers;

use App\Models\VesselVoyage;

class GetVoyageData
{
    /**
     * Function to get voyage data.
     *
     * @param string $voyageReferenceNumber
     * @return VesselVoyage
     */
    public static function execute($voyageReferenceNumber)
    {
        $voyage = VesselVoyage::with(
            'embarkPort',
            'disembarkPort',
            'prices',
            'prices.cabins',
            'vessel.cabins.prices'
        )->where('voyageReferenceNumber', $voyageReferenceNumber)
         ->first();

        return $voyage;
    }
}
