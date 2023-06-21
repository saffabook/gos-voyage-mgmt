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
            'voyageCabinPrices'
        )->where('voyageReferenceNumber', $voyageReferenceNumber)
         ->first();

        if (empty($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        return ApiResponse::success($voyage->toArray());
    }
}
