<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

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
            'id' => 'required|integer|exists:vessel_cabins,id,vessel_id,'.$request->vessel_id,
            'title' => [
                'string',
                'max:255',
                Rule::unique('vessel_cabins')
                    ->where('vessel_id', $request->vessel_id),
            ],
            'description'          => 'string|max:255',
            'max_occupancy'        => 'integer',
            'can_be_booked_single' => 'boolean',
            'vessel_id'            => 'required|integer|exists:vessels,id'
        ], [
            'title.unique' => 'This vessel already has a cabin with that name.',
            'vessel_id.exists' => 'This vessel does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();
        unset($validatedData['vessel_id']);
        $vesselCabin = VesselCabin::find($request->input('id'));
        $vesselCabin->fill($validatedData);
        $vesselCabin->save();

        return ApiResponse::success($vesselCabin, 'The cabin was updated');
    }
}
