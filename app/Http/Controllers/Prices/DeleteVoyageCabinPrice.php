<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;

class DeleteVoyageCabinPrice extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($priceId)
    {
        $price = VoyageCabinPrice::find($priceId);

        if (empty($price)) {
            return ApiResponse::error('Price not found.');
        }

        $price->delete();

        return ApiResponse::success('Price deleted successfully');
    }
}
