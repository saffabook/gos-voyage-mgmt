<?php

namespace App\Http\Controllers\Voyages;

use App\Helpers\ApiResponse;
use App\Helpers\GenerateVoyageId;
use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateVesselVoyage extends Controller
{
    /**
     * Create voyage primary data after checking whether the title is a
     * duplicate, the user/company is authorized to use the requested vessel
     * and ports, and that voyage dates make sense chronologically and do not
     * conflict with another voyage for the same vessel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Helpers\ApiResponse
     */
    public function __invoke(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'title'              => 'required|string|max:255',
            'description'        => 'string|between:30,600',
            'vesselId'           => 'required|integer',
            'voyageType'         => 'in:ROUNDTRIP,ONEWAY,DAYTRIP',
            'isPassportRequired' => 'boolean',
            'embarkPortId'       => 'required|integer',
            'disembarkPortId'    => 'required|integer',
            'startDate'          => 'required|date_format:Y-m-d',
            'startTime'          => 'required|date_format:H:i',
            'endDate'            => 'required|date_format:Y-m-d',
            'endTime'            => 'required|date_format:H:i',
            'forceAction'        => 'sometimes|in:1,true',
        ], [
            'forceAction.in' => 'forceAction can only be true'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        // Warn if title already exists.
        if (! isset($validatedData['forceAction'])) {

            $titleIsDuplicate = VesselVoyage::where(
                'companyId', $validatedData['companyId']
            )->where(
                DB::raw('lower(title)'), strtolower($validatedData['title'])
            )->exists();


            if ($titleIsDuplicate) {
                return ApiResponse::error(
                    'You have already created a voyage with that title. Please confirm you would like to create this voyage with the duplicate title.'
                );
            }
        }

        // Check vesselId validity.
        $vesselIsValid = Vessel::where(
            'companyId', $validatedData['companyId']
        )->find($validatedData['vesselId']);

        if (! $vesselIsValid) {
            return ApiResponse::error('The requested vessel is invalid.');
        }

        // Check embarkPortId validity.
        $embarkPortIsValid = VoyagePort::where(
            'companyId', $validatedData['companyId']
        )->find($validatedData['embarkPortId']);

        if (! $embarkPortIsValid) {
            return ApiResponse::error(
                'The requested embarkPortId is invalid.'
            );
        }

        // Check disembarkPortId validity.
        $disembarkPortIsValid = VoyagePort::where(
            'companyId', $validatedData['companyId']
        )->find($validatedData['disembarkPortId']);

        if (! $disembarkPortIsValid) {
            return ApiResponse::error(
                'The requested disembarkPortId is invalid.'
            );
        }

        // Check vesselId does not conflict with another voyage for the vessel.
        $vesselIsBooked = VesselVoyage::where(
            'vesselId', $validatedData['vesselId']
        )->whereDate('startDate', '<=', $validatedData['endDate'])
         ->whereDate('endDate', '>=', $validatedData['startDate'])
         ->exists();

        if (! empty($vesselIsBooked)) {
            return ApiResponse::error(
                'The requested vessel is already booked for this time.'
            );
        }

        // Ensure request startDate is earlier than endDate.
        if ($validatedData['startDate'] > $validatedData['endDate']) {
            return ApiResponse::error(
                "The voyage start date cannot be set later than the voyage end date."
            );
        }

        $validatedData['voyageReferenceNumber'] = GenerateVoyageId::execute(
            $request->input('companyId')
        );

        $voyage = VesselVoyage::create($validatedData);

        return ApiResponse::success(
            $voyage->toArray(), 'The voyage was created.'
        );
    }
}
