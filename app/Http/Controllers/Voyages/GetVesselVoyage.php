<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VesselVoyage;
use App\Helpers\ApiResponse;

class GetVesselVoyage extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($voyageReferenceNumber)
    {
        $voyage = VesselVoyage::with(
            'embarkPort',
            'disembarkPort',
            'prices',
            'prices.cabins',
            'vessel.cabins.prices'
        )->where('voyageReferenceNumber', $voyageReferenceNumber)
         ->first();

        if (empty($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        unset(
            $voyage->embarkPort['companyId'],
            $voyage->disembarkPort['companyId'],
            $voyage->vessel['companyId']
        );

        foreach ($voyage->prices as $price) {
            unset($price['companyId']);
            foreach ($price['cabins'] as $cabin) {
                unset($cabin['pivot']);
            }
        }

        foreach ($voyage->vessel['cabins'] as $cabin) {
            foreach ($cabin['prices'] as $price) {
                unset($price['pivot']);
            }
        }

        return ApiResponse::success($voyage->toArray());
    }
}
