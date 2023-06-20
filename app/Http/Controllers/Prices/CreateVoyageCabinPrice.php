<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Helpers\CheckSimilarWords;
use App\Helpers\GetCompanyVoyageById;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use Carbon\Carbon;
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
            'title'                => 'required|string|max:255',
            'description'          => 'string|max:255',
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

        $cabin = $voyage->vessel->cabins->where('id', $request->cabinId)->first();

        if (is_null($cabin)) {
            return ApiResponse::error('Cabin not found');
        }

        if (!empty($cabin->prices)) {
            foreach ($cabin->prices as $prices) {
                if ($prices->title === $request->title) {
                    return ApiResponse::error(
                        "The title '{$prices->title}' already exists'. Please create a different title."
                    );
                }
                if (CheckSimilarWords::execute($request->title, $prices->title, 3)) {
                    return ApiResponse::error(
                        "The title '{$request->title}' is too similar to '{$prices->title}'. Please create a different title."
                    );
                }
            }
        }

        $price = VoyageCabinPrice::create($validatedData);

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
