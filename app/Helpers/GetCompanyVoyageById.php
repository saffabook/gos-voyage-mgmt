<?php

namespace App\Helpers;

use App\Models\Vessel;
use App\Models\VesselVoyage;

/**
 * TODO doc block
 */
class GetCompanyVoyageById
{   
    /**
     * 
     */
    public static function execute($companyId, $voyageId)
    {

        return VesselVoyage::where('companyId', $companyId)
                                ->with('embarkPort', 'disembarkPort')
                                ->with('vessel', 'vessel.cabins', 'vessel.cabins.cabinPrices')
                                ->find($voyageId);

    }
}
