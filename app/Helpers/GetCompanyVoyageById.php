<?php

namespace App\Helpers;

use App\Models\Vessel;
use App\Models\VesselVoyage;

/**
 * Undocumented class
 */
class GetCompanyVoyageById
{
    /**
     * Undocumented function
     *
     * @param [type] $companyId
     * @param [type] $voyageId
     * @return void
     */
    public static function execute($companyId, $voyageId)
    {
        return VesselVoyage::where('companyId', $companyId)
                           ->with('embarkPort', 'disembarkPort')
                           ->with('vessel', 'vessel.cabins', 'vessel.cabins.cabinPrices')
                           ->find($voyageId);
    }
}
