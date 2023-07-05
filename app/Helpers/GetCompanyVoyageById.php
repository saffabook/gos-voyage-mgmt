<?php

namespace App\Helpers;

use App\Models\VesselVoyage;

class GetCompanyVoyageById
{
    /**
     * Function to get voyage with vessel, cabins and cabin prices
     *
     * @param int $companyId
     * @param int $voyageId
     * @return VesselVoyage
     */
    public static function execute($companyId, $voyageId)
    {
        $voyageData = VesselVoyage::where('companyId', $companyId)
                                  ->with('embarkPort', 'disembarkPort')
                                  ->with('prices')
                                  ->with('prices.cabins')
                                  ->with(['vessel.cabins.prices' => function ($query) use ($voyageId) {
                                      $query->where('voyageId', $voyageId);
                                  }])->find($voyageId);

        return $voyageData;
    }
}
