<?php

namespace App\Http\Controllers\Cabins\MetaData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabinAdditionals;
use App\Helpers\ApiResponse;

class UpdateVesselCabinAdditionals extends Controller
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
            'id' => 'required|integer|
                exists:vessel_cabin_additionals,id,cabin_id,'.
                $request->cabin_id,
            'title'       => 'string|max:255',
            'description' => 'string|max:255',
            'cabin_id'    => 'required|integer|exists:vessel_cabins,id'
        ], [
            'cabin_id.exists' => 'This cabin does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();
        unset($validatedData['cabin_id']);
        $vesselCabinAdditionals = VesselCabinAdditionals::find(
            $request->input('id')
        );
        $vesselCabinAdditionals->fill($validatedData);
        $vesselCabinAdditionals->save();

        return ApiResponse::success(
            $vesselCabinAdditionals->toArray(), 'Cabin information updated'
        );
    }
}
