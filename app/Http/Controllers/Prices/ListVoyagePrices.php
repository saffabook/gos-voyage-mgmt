<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyagePrice;
use Illuminate\Http\Request;

class ListVoyagePrices extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $pricesFromDb = VoyagePrice::where(
            'companyId', $request->input('companyId')
        )->with('voyage')->get();

        if ($pricesFromDb->isEmpty()) {
          return ApiResponse::error('No prices found');
        }

        return ApiResponse::success($pricesFromDb->toArray());
    }
}
