<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VesselVoyage;
use App\Helpers\ApiResponse;
use App\Models\Vessel;
use Illuminate\Validation\Rule;
use App\Helpers\GenerateVoyageId;

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
        $companyId = 0;

        $validatedData = Validator::make($request->all(), [
            'title'              => 'required|string|unique:vessel_voyages|max:255',
            'description'        => 'string|between:30,600',
            'vesselId'           => 'required|integer',
            'voyageType'         => 'in:ROUNDTRIP,ONEWAY,DAYTRIP',
            'isPassportRequired' => 'boolean',
            'embarkPortId'       => 'required|integer|exists:voyage_ports,id',
            'disembarkPortId'    => 'required|integer|exists:voyage_ports,id',
            'startDate'          => 'required|date_format:Y-m-d',
            'startTime'          => 'required|date_format:H:i',
            'endDate'            => 'required|date_format:Y-m-d',
            'endTime'            => 'required|date_format:H:i',
        ], [
            'title.unique'           => 'A voyage with that name already exists.',
            'embarkPortId.exists'    => 'The embark port does not exist.',
            'disembarkPortId.exists' => 'The disembark port does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $vessel = Vessel::where('company_id', $companyId)
                        ->find($request->input('vesselId'));

        if (!$vessel) {
            return ApiResponse::error('Vessel does not exist');
        }

        $validatedData = $validatedData->validated();

        $vesselIsBooked = VesselVoyage::where('vesselId', $validatedData['vesselId'])
            ->whereDate('startDate', '<=', $validatedData['endDate'])
            ->whereDate('endDate', '>=', $validatedData['startDate'])
            ->exists();

        if (!empty($vesselIsBooked)) {
            return ApiResponse::error('The vessel is already booked for this time');
        }

        $validatedData['voyageReferenceNumber'] = GenerateVoyageId::execute($companyId);

        $voyage = VesselVoyage::create($validatedData);

        return ApiResponse::success(
            $voyage->toArray(), 'The voyage was created'
        );
    }
}
