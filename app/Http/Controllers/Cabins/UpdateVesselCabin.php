<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;

class UpdateVesselCabin extends Controller
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
            'id' => 'required|integer|exists:vessel_cabins,id',
            'title' => 'string|max:255|unique:vessel_cabins,title,'.$request->id,
            'description' => 'string|max:255',
            'max_occupancy' => 'integer',
            'can_be_booked_single' => 'boolean',
            'vessel_id' => 'integer'
        ], [
            'title.unique' => 'A cabin with that name already exists.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $vesselCabin = VesselCabin::find($request->input('id'));
        $vesselCabin->fill($validatedData->validated());
        $vesselCabin->save();

        return ApiResponse::success($vesselCabin, 'The cabin was updated');
    }
}
