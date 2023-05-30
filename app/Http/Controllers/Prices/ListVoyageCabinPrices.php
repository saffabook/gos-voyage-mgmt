<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;

class ListVoyageCabinPrices extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $pricesFromDb = VoyageCabinPrice::get();

        if ($pricesFromDb->isEmpty()) {
          return ApiResponse::error('No prices found');
        }

        return ApiResponse::success($pricesFromDb->toArray());
    }
}
