<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;

class GetVoyageCabinPrice extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $price = VoyageCabinPrice::find($id);

        if (empty($price)) {
            return ApiResponse::error('Price not found');
        }

        return ApiResponse::success($price->toArray());
    }
}
