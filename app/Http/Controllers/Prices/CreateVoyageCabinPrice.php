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

        $companyIdFromJwt = 1;

        $voyage =  GetCompanyVoyageById::execute(
            $companyIdFromJwt, 1
        );

        if(is_null($voyage)){
            return ApiResponse::error('Voyage not found');
        }
        
        $cabin = $voyage->vessel->cabins->where('id', $request->cabinId)->first();

        foreach($cabin->cabinPrices as $prices){
            if($prices->title === $request->title){
                return ApiResponse::error(
                    "The title '{$prices->title}' already exists'. Please create a different title."
                );
            }
            if(CheckSimilarWords::execute($request->title, $prices->title)){
                return ApiResponse::error(
                    "The title '{$prices->title}' is too similar to '{$request->title}'. Please create a different title."
                );
            }
        }  

        
        // $price = VoyageCabinPrice::create($validatedData);

        //respond success

        var_dump($cabin->toArray());


        die();
        $validatedData = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
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

        

        // $price = VoyageCabinPrice::create($validatedData);

        // return ApiResponse::success($price->toArray(), 'The price was created');
        return ApiResponse::success($response);
    }
}
