<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Helpers\GetVoyageData;

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
        $voyage = GetVoyageData::execute($voyageReferenceNumber);

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

        return ApiResponse::success($voyage->toArray());
    }
}
