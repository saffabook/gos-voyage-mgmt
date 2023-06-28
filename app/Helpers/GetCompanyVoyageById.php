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
        return VesselVoyage::where('companyId', $companyId)
                            ->find($voyageId);
                            //->with('embarkPort', 'disembarkPort')
                            // ->with(['vessel.cabins.prices' => function ($query) use ($voyageId) {
                            //    $query->where('voyageId', $voyageId);
                            // }])->find($voyageId);
    }
}
