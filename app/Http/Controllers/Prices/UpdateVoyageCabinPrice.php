<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Carbon\Carbon;
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
    public function __invoke($id, Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'currency'             => 'required|string',
            'priceMinor'           => 'sometimes|integer|gt:0',
            'discountedPriceMinor' => 'sometimes|integer|gt:0',
            'forceAction'          => 'sometimes|in:1,true',
        ], [
            'forceAction.in' => 'forceAction can only be true'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $price = VoyageCabinPrice::where('companyId', $request->companyId)
                                 ->with('voyage')
                                 ->find($id);

        if (empty($price)) {
            return ApiResponse::error('Price not found.');
        }

        $voyageIsActive = $price->voyage->voyageStatus === 'ACTIVE'
                       && $price->voyage->endDate >= Carbon::now()
                        ? true : false;

        if ($voyageIsActive && isset($validatedData['priceMinor']) && empty($validatedData['forceAction'])) {

            if (! isset($validatedData['discountedPriceMinor'])) {
                return ApiResponse::error('Voyage is active. Do you want to add a discount price?');
            }
            return ApiResponse::error('This voyage is active. Are you sure you want to update?');
        }

        if (isset($validatedData['discountedPriceMinor'])) {
            $priceMinor = isset($validatedData['priceMinor'])
                        ? $validatedData['priceMinor']
                        : $price->priceMinor;

            if ($priceMinor <= $validatedData['discountedPriceMinor']) {
                return ApiResponse::error('Discounted price must be less than original price.');
            }
        }

        if (! isset($validatedData['discountedPriceMinor'])) {
            $validatedData['discountedPriceMinor'] = null;
        }

        $price->fill($validatedData);
        $price->save();

        return ApiResponse::success(
            $price->toArray(), 'The price has been updated.'
        );
    }
}
