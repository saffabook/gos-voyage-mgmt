<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

class CreateVesselCabin extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * TODO ensure number of cabins does not exceed vessel capacity
     */
    public function __invoke(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vessel_cabins')
                    ->where('vessel_id', $request->vessel_id),
            ],
            'description'          => 'string|max:255',
            'max_occupancy'        => 'required|integer',
            'can_be_booked_single' => 'boolean',
            'vessel_id'            => 'required|integer|exists:vessels,id'
        ], [
            'title.unique' => 'This vessel already has a cabin with that name.',
            'vessel_id.exists' => 'This vessel does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $cabin = VesselCabin::create($validatedData->validated());

        return ApiResponse::success($cabin->toArray(), 'The cabin was created');
    }
}
