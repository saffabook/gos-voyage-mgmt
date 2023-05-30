<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateVoyageCabinPrice extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($priceId, Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'cabinId'              => 'required|integer',
            'voyageId'             => 'required|integer',
            'currency'             => 'required|string',
            'priceMinor'           => 'required|integer',
            'discountedPriceMinor' => 'integer'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $price = VoyageCabinPrice::find($priceId);

        if (empty($price)) {
            return ApiResponse::error('Price not found.');
        }

        $price->fill($validatedData);
        $price->save();

        return ApiResponse::success(
            $price->toArray(), 'The price has been updated.'
        );
    }
}
