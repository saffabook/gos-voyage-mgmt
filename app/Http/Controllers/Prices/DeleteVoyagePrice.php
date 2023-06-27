<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyagePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeleteVoyagePrice extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id, Request $request)
    {
        $price = VoyagePrice::where('companyId', $request->companyId)
                                 ->with('voyage')
                                 ->find($id);

        if (empty($price)) {
            return ApiResponse::error('Price not found.');
        }

        $voyageIsActive = $price->voyage->voyageStatus === 'ACTIVE'
                       && $price->voyage->endDate >= Carbon::now();

        if ($voyageIsActive) {
            return ApiResponse::error(
                'Voyage is active. Convert status to draft or cancelled.'
            );
        }

        $price->delete();

        return ApiResponse::success('Price deleted successfully');
    }
}
