<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Helpers\AttachPriceCabin;
use App\Helpers\CheckSimilarWords;
use App\Helpers\GetCompanyVoyageById;
use App\Http\Controllers\Controller;
use App\Models\VesselCabin;
use App\Models\VoyagePrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateVoyagePrice extends Controller
{
    /**
     * Handle the incoming request, validate request data, perform necessary
     * checking logic, create price and attach requested cabins to
     * the newly created price.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Helpers\ApiResponse
     */
    public function __invoke(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title'                => 'required|string|max:255',
            'description'          => 'string|max:255',
            'cabinIds'             => 'required|array|min:1',
            'cabinIds.*'           => 'integer',
            'voyageId'             => 'required|integer',
            'currency'             => 'required|string',
            'priceMinor'           => 'required|integer',
            'discountedPriceMinor' => 'integer'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        $voyage = GetCompanyVoyageById::execute(
            $validatedData['companyId'], $validatedData['voyageId']
        );

        if (is_null($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        if ($voyage['endDate'] < Carbon::now()) {
            return ApiResponse::error(
                "The voyage '{$voyage['title']}' has expired."
            );
        }

        // Check ids for cabins before creating (no overlap).
        $vesselCabinIds = collect($voyage->vessel->cabins)->pluck('id')->toArray();
        $cabinIdsToBePriced = array_map('intval', $validatedData['cabinIds']);

        // Check to see if cabin id belongs to the vessel.
        foreach($cabinIdsToBePriced as $cabinIdToPrice) {
            if(! in_array($cabinIdToPrice, $vesselCabinIds)) {
                return ApiResponse::error('Cabin id '. $cabinIdToPrice.' does not belong to the selected vessel. You cannot add a price to this cabin');
            }

            // Check cabin's price titles for duplicates on requested voyage.
            if (in_array($cabinIdToPrice, $vesselCabinIds)) {
                $cabin = VesselCabin::where('id', $cabinIdToPrice)->with('prices')->get();

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

        $price = VoyagePrice::create($validatedData);

        foreach($cabinIdsToBePriced as $cabinIdToPrice) {
            AttachPriceCabin::execute($price->id, $cabinIdToPrice);
        }

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
