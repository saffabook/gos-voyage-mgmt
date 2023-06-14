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

        return VesselVoyage::with(['vessel.cabins.prices' => function ($query) use ($voyageId) {
            $query->where('voyageId', $voyageId);
        }])->find($voyageId);

    }
}
