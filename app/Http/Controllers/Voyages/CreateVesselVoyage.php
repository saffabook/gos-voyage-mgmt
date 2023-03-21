<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselVoyage;
use App\Helpers\ApiResponse;

class CreateVesselVoyage extends Controller
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
            'title' => 'required|string|unique:vessel_voyages|max:255',
            'description' => 'string|between:30,600',
            'vesselId' => 'required|integer',
            'voyageType' => 'required|string',
            'voyageReferenceNumber' => 'required|string',
            'isPassportRequired' => 'boolean',
            'embarkPortId' => 'required|integer',
            'startDate' => 'required|date_format:Y-m-d',
            'startTime' => 'required|date_format:H:i',
            'disembarkPortId' => 'required|integer',
            'endDate' => 'required|date_format:Y-m-d',
            'endTime' => 'required|date_format:H:i',
        ], [
            'name.unique' => 'A voyage with that name already exists.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $voyage = VesselVoyage::create($validatedData->validated());

        return ApiResponse::success($voyage, 'The voyage was created');
    }
}
