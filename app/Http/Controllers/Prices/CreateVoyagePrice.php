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
            'cabinIds'             => 'required|array|min:1',
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


        var_dump($voyage->toArray());


        //expectedResults 

        //$voyage = [
        //   ["id"]=> int(1)
        //   ["title"]=> string(13) "Finland coast"
        //   [...] => rest 
        //   [pricesWithCabins] => [
        //      [0] => [
        //         [title] => 'adults'   
        //         [priceValue] => 100
        //         [cabins] => [
        //                [0] => [ name => 'port cabin', id:1],         pivot_setup cabinId:1 priceId:1
        //                [1] => [ name => 'starboard cabin', id:2],    pivot_setup cabinId:2 priceId:1
        //                [2] => [ name => 'aft cabin', id:3],          pivot_setup cabinId:3 priceId:1   
        //         ]
        //      ]
        //      [1] => [
        //         [title] => 'kids'
        //         [priceValue] => 50
        //         [cabins] => [
        //                [0] => [ name => 'port cabin', id:1],         pivot_setup cabinId:1 priceId:2
        //                [1] => [ name => 'starboard cabin', id:2]     pivot_setup cabinId:2 priceId:2
        //         ]
        //      ]
        //      [2] => [
        //          [title] => 'adults'
        //          [priceValue] => 1000
        //          [cabins] => [
        //                [3] => [ name => 'stern cabin', id:4]         pivot_setup cabinId:4 priceId:3
        //         ]
        //      ] 
        //   ]
        //]


        die();

        if (is_null($voyage)) {
            return ApiResponse::error('Voyage not found');
        }

        if ($voyage['endDate'] < Carbon::now()) {
            return ApiResponse::error(
                "The voyage '{$voyage['title']}' has expired."
            );
        }


        $vesselCabinIds = collect($voyage->vessel->cabins)->pluck('id')->toArray();

        $cabinIdsToBePriced = array_map('intval', $validatedData['cabinIds']);

        /// STUPID IMPORTANT PIECE OF CODE: ENSURE THIS IS TESTED LIKE MAD
        foreach($cabinIdsToBePriced as $cabinIdToPrice){
            if(!in_array($cabinIdToPrice, $vesselCabinIds)){ // check to see if cabin id belongs to the vessel
                return ApiResponse::error('Cabin id '. $cabinIdToPrice.' does not belong to vessel. You cannot add a price to this cabin');
            }
        }

        


        // if (!empty($cabin->prices)) {
        //     foreach ($cabin->prices as $prices) {
        //         if ($prices->title === $request->title) {
        //             return ApiResponse::error(
        //                 "The title '{$prices->title}' already exists'. Please create a different title."
        //             );
        //         }
        //         if (CheckSimilarWords::execute($request->title, $prices->title, 3)) {
        //             return ApiResponse::error(
        //                 "The title '{$request->title}' is too similar to '{$prices->title}'. Please create a different title."
        //             );
        //         }
        //     }
        // }

        //$price = VoyagePrice::create($validatedData);
        // $cabin = VesselCabin::find([$request->get('cabinId')]);

        //AttachPriceCabin::execute($price->id, $cabin->id);

        return ApiResponse::success($price->toArray(), 'The price was created');
    }
}
