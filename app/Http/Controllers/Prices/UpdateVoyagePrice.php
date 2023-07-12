<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Helpers\CheckSimilarWords;
use App\Helpers\GetCompanyVoyageById;
use App\Http\Controllers\Controller;
use App\Models\VesselCabin;
use App\Models\VoyagePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateVoyagePrice extends Controller
{
    /**
     * Update a price for a voyage after checking whether the voyage is active,
     * title is not a duplicate, discounted price is set lower than original price,
     * suggesting to add discount price rather than lowering original price, etc.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id, Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title'                => 'string|max:255',
            'description'          => 'string|max:255',
            'voyageId'             => 'required|integer',
            'currency'             => 'string|max:255',
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

        $validatedData['companyId'] = $request->input('companyId');

        $price = VoyagePrice::where('companyId', $request->companyId)
                            ->with('cabins')
                            ->find($id);

        if (empty($price)) {
            return ApiResponse::error('Price not found.');
        }

        if (isset($validatedData['discountedPriceMinor'])) {
            $priceMinor = isset($validatedData['priceMinor'])
                        ? $validatedData['priceMinor']
                        : $price->priceMinor;

            if ($priceMinor <= $validatedData['discountedPriceMinor']) {
                return ApiResponse::error('Discounted price must be lower than original price.');
            }
        }

        if (! isset($validatedData['discountedPriceMinor'])) {
            $validatedData['discountedPriceMinor'] = null;
        }

        $voyage = GetCompanyVoyageById::execute(
            $validatedData['companyId'], $price['voyageId']
        );

        if ($voyage['endDate'] < Carbon::now()) {
            return ApiResponse::error(
                "The voyage '{$voyage['title']}' has expired."
            );
        }

        $voyageIsActive = $voyage->voyageStatus === 'ACTIVE'
                       && $voyage->endDate >= Carbon::now()
                        ? true : false;

        if ($voyageIsActive && isset($validatedData['priceMinor']) && empty($validatedData['forceAction'])) {

            if (! isset($validatedData['discountedPriceMinor'])) {
                return ApiResponse::error('This voyage is active. Price can only be updated with forceAction. Would you like to add a promotional price?');
            }
            return ApiResponse::error('This voyage is active. Price can only be updated with forceAction.');
        }

        // If request contains updated title, check cabin price titles for duplicates.
        if (isset($validatedData['title'])) {
            $vesselCabinIds = collect($voyage->vessel->cabins)->pluck('id')->toArray();
            $cabinIdsToBeUpdated = collect($price->cabins)->pluck('id')->toArray();

            foreach($cabinIdsToBeUpdated as $cabinId) {

                // Check cabin's price titles for duplicates on requested voyage.
                if (in_array($cabinId, $vesselCabinIds)) {
                    $cabin = VesselCabin::where('id', $cabinId)->with('prices')->get();
                    $firstCabin = $cabin->first();

                    foreach ($firstCabin->prices as $price) {
                        if (intval($price->voyageId) !== intval($validatedData['voyageId'])) {
                            continue;
                        }

                        if (CheckSimilarWords::execute($price->title, $validatedData['title'], 3)) {
                            return ApiResponse::error(
                                ['errorType' => 'Price title match'],
                                "The cabin '{$firstCabin->title}' already has a price for this voyage called '{$price->title}', which is too similar to '{$validatedData['title']}'. Please create a different title."
                            );
                        }
                    }
                }
            }
        }

        $price->fill($validatedData);
        $price->save();

        unset($price->cabins);

        return ApiResponse::success(
            $price->toArray(), 'The price has been updated.'
        );
    }
}
