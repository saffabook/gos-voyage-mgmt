<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;

class CreateVesselCabin extends Controller
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
            'description' => 'required|string|max:255',
            'max_occupancy' => 'required|integer',
            'can_be_booked_single' => 'boolean',
            'vessel_id' => 'required|integer'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $cabin = VesselCabin::create($validatedData->validated());

        return ApiResponse::success($cabin, 'The cabin was added');
    }
}
