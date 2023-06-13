<?php

namespace App\Helpers;

use App\Models\Vessel;
use App\Models\VesselVoyage;

class GetCompanyVoyageById
{
    public static function execute($companyId, $voyageId)
    {
        $responseData = [];

        $voyage = VesselVoyage::with('voyageCabinPrices')
                              ->where('companyId', $companyId)
                              ->find($voyageId);

        $responseData['voyage'] = $voyage;

        $vessel = Vessel::with('cabins')
                        ->where('id', $voyage['vesselId'])
                        ->where('companyId', $voyage['companyId'])
                        ->get();

        $responseData['vessel'] = $vessel;

        return response()->json(['data' => $responseData]);
    }
}
