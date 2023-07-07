<?php

namespace App\Helpers;

use App\Models\VesselVoyage;

class GetVoyageData
{
    /**
     * Helper to get complete voyage data from the database by
     * voyageReferenceNumber.
     *
     * @param string $voyageReferenceNumber
     * @return VesselVoyage
     */
    public static function execute($voyageReferenceNumber)
    {
        $voyage = VesselVoyage::with(
            'vessel.cabins',
            'embarkPort',
            'disembarkPort',
            'prices.cabins',
        )->where('voyageReferenceNumber', $voyageReferenceNumber)
         ->first();

        return $voyage;
    }
}
