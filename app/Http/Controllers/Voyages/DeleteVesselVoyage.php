<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Helpers\GetCompanyVoyageById;
use Carbon\Carbon;

class DeleteVesselVoyage extends Controller
{
    /**
     * Delete voyage data and voyage's price records, after checking whether
     * the voyage is active or expired.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($voyageId, Request $request)
    {

        $voyage = GetCompanyVoyageById::execute(
            $request['companyId'], $voyageId
        );

        if (is_null($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        $voyageIsActive = $voyage->voyageStatus === 'ACTIVE'
                       && $voyage->endDate >= Carbon::now();

        if ($voyageIsActive) {
            return ApiResponse::error(
                'Voyage is active. Convert status to draft or cancelled.'
            );
        }

        $voyageIsExpired = $voyage->voyageStatus === 'ACTIVE'
                        && $voyage->endDate < Carbon::now();

        if ($voyageIsExpired) {
            return ApiResponse::error(
                'This voyage has expired. It cannot be deleted.'
            );
        }

        foreach($voyage->prices as $price) {
            $price->cabins()->detach();
            $price->delete();
        }

        $voyage->delete();

        return ApiResponse::success('Voyage deleted successfully');
    }
}
