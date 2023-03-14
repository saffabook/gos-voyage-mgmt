<?php

namespace App\Http\Controllers\Cabins\MetaData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabinAdditionals;
use App\Helpers\ApiResponse;

class CreateVesselCabinAdditionals extends Controller
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
            'title'       => 'required|string|max:255',
            'description' => 'string|max:255',
            'cabin_id'    => 'required|integer|exists:vessel_cabins,id'
        ], [
            'cabin_id.exists' => 'This cabin does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $cabinAdditionals = VesselCabinAdditionals::create(
            $validatedData->validated()
        );

        return ApiResponse::success($cabinAdditionals, 'Cabin information added');
    }
}
