<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
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
            // 'title'                => 'required|string|max:255',
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
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $cabin = VesselCabin::where('id', $request->cabinId)->find();

        if ($cabin->companyId !== $request->companyId) {
            return ApiResponse::error('Cabin not found');
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        // $cabinId = $validatedData['cabinId'];

        // $voyageWithCabins = VesselVoyage::with([
        //     'vesselCabins' => function ($query) use ($cabinId) {
        //         $query->where('id', $cabinId)->with(['cabinPrices'])->first();
        //     }
        // ])->where('companyId', $request->companyId)->find($request->voyageId);

        // TODO: Refactor DB call to get voyage with specific cabin not in array.

        // foreach ($voyageWithCabins->vesselCabins as $cabin) {
        //     // var_dump($cabin->toArray());
        //     foreach ($cabin->cabinPrices as $prices) {
        //         // var_dump($prices->toArray());
        //         if ($prices->title === $validatedData['title']) {
        //             return ApiResponse::error('Price title must be unique for cabin and voyage');
        //         }
        //     }
        // }

        $price = VoyageCabinPrice::create($validatedData);

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
