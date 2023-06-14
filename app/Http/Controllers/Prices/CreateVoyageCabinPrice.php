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
            'title'                => 'required|string|max:255',
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

        $validatedData = $validatedData->validated();

        // $companyIdFromJwt = 1;
        $validatedData['companyId'] = $request->input('companyId');

        $voyage = GetCompanyVoyageById::execute(
            // $companyIdFromJwt, 1
            $validatedData['companyId'], $validatedData['voyageId']
        );

        var_dump($voyage->vessel->cabins->toArray());
        exit;

        if (is_null($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        $cabin = $voyage->vessel->cabins->where('id', $request->cabinId)->first();

        foreach ($cabin->cabinPrices as $prices) {
            if ($prices->title === $request->title) {
                return ApiResponse::error(
                    "The title '{$prices->title}' already exists'. Please create a different title."
                );
            }
            if (CheckSimilarWords::execute($request->title, $prices->title)) {
                return ApiResponse::error(
                    "The title '{$prices->title}' is too similar to '{$request->title}'. Please create a different title."
                );
            }
        }

        $price = VoyageCabinPrice::create($validatedData);

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
