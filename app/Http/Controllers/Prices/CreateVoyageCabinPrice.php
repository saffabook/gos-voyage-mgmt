<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateVoyageCabinPrice extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'cabinId'              => 'required|integer|exists:vessel_cabins,id',
            'voyageId'             => 'required|integer|exists:vessel_voyages,id',
            'currency'             => 'required|string',
            'priceMinor'           => 'required|integer',
            'discountedPriceMinor' => 'integer'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        $price = VoyageCabinPrice::create($validatedData);

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
