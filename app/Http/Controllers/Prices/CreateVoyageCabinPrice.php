<?php

namespace App\Http\Controllers\Prices;

use App\Helpers\ApiResponse;
use App\Helpers\CheckSimilarWords;
use App\Http\Controllers\Controller;
use App\Models\VoyageCabinPrice;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
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

        $companyId = 1;
        $validatedData = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'string',
                'max:255',
            ],
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

        $validatedData['companyId'] = $companyId;

        //$voyage = VesselVoyage::find($validatedData['voyageId']);

        $cabinId = $validatedData['cabinId'];

        $voyage = VesselVoyage::whereHas('vesselCabins', function ($query) use ($cabinId) {
            $query->where('id', $cabinId);
        })->get();


        $posts = VesselVoyage::join('vessel_cabins', 'vessels.id', '=', 'vessel_cabins.vessel_id')
                        ->where('vessel_cabins.vessel_id', $cabinId)
                        ->get();


        //Get the vessel voyage
        //Get the cabin
        //Get the price 
        
        

        var_dump($posts->toArray());



        // $cabin = VesselCabin::with('cabinPrices')
        //                     ->with('vesselVoyage')
        //                     ->find($validatedData['cabinId']);


        die();

        //if cabin does not have a price attached, we can create a cabin price
        if(!$cabin->cabinPrices->isEmpty()){
            $cabinsCheckTitle = $cabin->cabinPrices->toArray();
            $titles = collect($cabinsCheckTitle)->pluck('title')->all();

            if(in_array($validatedData['title'], $titles)){
                return ApiResponse::error('The title '.$validatedData['title'].' for that cabin exists');
            }
            foreach($titles as $title){
                if(CheckSimilarWords::execute($title, $validatedData['title'])){
                    return ApiResponse::error('The title '.$validatedData['title'].' is too similar to '.$title.'. Please create a new title');        
                }
            }
        }

        $prices = VoyageCabinPrice::create($validatedData);

        return ApiResponse::success($prices->toArray(), 'The price was created');
    }
}
