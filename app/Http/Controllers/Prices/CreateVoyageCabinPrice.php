<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Helpers\CheckSimilarWords;
use App\Helpers\GetCompanyVoyageById;
use App\Http\Controllers\Controller;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
use App\Models\VoyageCabinPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('voyage_cabin_prices')
                    ->where('cabinId', $request->cabinId)
                    ->where('voyageId', $request->voyageId)
            ],
            'description'          => 'string|max:255',
            'cabinId'              => 'required|integer|exists:vessel_cabins,id',
            'voyageId'             => 'required|integer|exists:vessel_voyages,id',
            'currency'             => 'required|string',
            'priceMinor'           => 'required|integer',
            'discountedPriceMinor' => 'integer'
        ], [
            'title.unique' => "The title '{$request['title']}' for that cabin exists."
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $cabin = VesselCabin::where('id', $request->cabinId)
                            ->where('companyId', $request->companyId)
                            ->first();

        if (!$cabin) {
            return ApiResponse::error('Cabin not found');
        }

        $validatedData = $validatedData->validated();
        $validatedData['companyId'] = $request->input('companyId');

        $cabinsCheckTitle = $cabin->cabinPrices->where('voyageId', $validatedData['voyageId'])->toArray();
        $titles = collect($cabinsCheckTitle)->pluck('title')->all();

        foreach($titles as $title) {
            if(CheckSimilarWords::execute($title, $validatedData['title'])) {
                return ApiResponse::error(
                    "The title '{$validatedData['title']}' is too similar to '{$title}'. Please create a different title."
                );
            }
        }

        return GetCompanyVoyageById::execute(
            $validatedData['companyId'], $validatedData['voyageId']
        );

        // $price = VoyageCabinPrice::create($validatedData);

        // return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
