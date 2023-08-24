<?php

namespace App\Http\Controllers\Voyages;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateVesselVoyage extends Controller
{
    /**
     * Update a voyage's primary data after checking user/companyId is
     * authorized to do so, requested data makes sense logically and that
     * requested update does not conflict with any existing voyage data (eg.
     * vessel booking for other voyages).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Helpers\ApiResponse
     */
    public function __invoke($id, Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'title'              => 'string|max:255|',
            'description'        => 'string|between:30,600',
            'vesselId'           => 'integer|exists:vessels,id',
            'voyageType'         => 'in:ROUNDTRIP,ONEWAY,DAYTRIP',
            'isPassportRequired' => 'boolean',
            'embarkPortId'       => 'integer|exists:voyage_ports,id',
            'disembarkPortId'    => 'integer|exists:voyage_ports,id',
            'startDate'          => 'date_format:Y-m-d',
            'startTime'          => 'date_format:H:i',
            'endDate'            => 'date_format:Y-m-d',
            'endTime'            => 'date_format:H:i',
            'voyageStatus'       => 'in:DRAFT,ACTIVE,CANCELLED',
            'forceAction'        => 'sometimes|in:1,true',
        ], [
            'forceAction.in' => 'forceAction can only be true'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        $voyage = VesselVoyage::where('companyId', $validatedData['companyId'])
                              ->find($id);

        if (is_null($voyage)) {
            return ApiResponse::error("Voyage not found.");
        }

        // Warn if title already exists.
        if (! isset($validatedData['forceAction']) && isset($validatedData['title'])) {

            $titleIsDuplicate = VesselVoyage::where(
                'companyId', $validatedData['companyId']
            )->where(
                DB::raw('lower(title)'), strtolower($validatedData['title'])
            )->exists();


            if ($titleIsDuplicate) {
                return ApiResponse::error(
                    'You have already created a voyage with that title. Please confirm you would like to update this voyage with the duplicate title.'
                );
            }
        }

        // Check vesselId validity.
        if (isset($validatedData['vesselId'])) {

            // Check vesselId is valid for companyId.
            $vesselIsValid = Vessel::where(
                'id', $validatedData['vesselId']
            )->where('companyId', $validatedData['companyId'])->exists();

            if (! $vesselIsValid) {
                return ApiResponse::error(
                    "The requested vessel is invalid."
                );
            }

            // Check vesselId does not conflict with another voyage for the vessel.
            $vesselIsBooked = VesselVoyage::where(
                'vesselId', $validatedData['vesselId']
            )->whereDate('startDate', '<=', $voyage['endDate'])
             ->whereDate('endDate', '>=', $voyage['startDate'])
             ->exists();

            if ($vesselIsBooked) {
                return ApiResponse::error(
                    "The requested vessel is already booked for this time."
                );
            }
        }

        // Check embarkPortId validity.
        if (isset($validatedData['embarkPortId'])) {

            $embarkPortIsValid = VoyagePort::where(
                'id', $validatedData['embarkPortId']
            )->where('companyId', $validatedData['companyId'])->exists();

            if (! $embarkPortIsValid) {
                return ApiResponse::error(
                    "Port is invalid."
                );
            }
        }

        // Check disembarkPortId validity.
        if (isset($validatedData['disembarkPortId'])) {

            $disembarkPortIsValid = VoyagePort::where(
                'id', $validatedData['disembarkPortId']
            )->where('companyId', $validatedData['companyId'])->exists();

            if (! $disembarkPortIsValid) {
                return ApiResponse::error(
                    "Port is invalid."
                );
            }
        }

        // Check dates if only updating startDate.
        if (isset($validatedData['startDate']) && ! isset($validatedData['endDate'])) {

            // Ensure request startDate is earlier than endDate.
            if ($validatedData['startDate'] > $voyage['endDate']) {
                return ApiResponse::error(
                    "The voyage start date cannot be set later than the voyage end date."
                );
            }

            // Ensure request startDate does not conflict with another voyage for the vessel.
            $vesselIsBooked = VesselVoyage::where(
                'vesselId', $voyage['vesselId']
            )->where('id', '!=', $id)
             ->whereDate('startDate', '<=', $validatedData['startDate'])
             ->whereDate('endDate', '>=', $validatedData['startDate'])
             ->exists();

            if ($vesselIsBooked) {
                return ApiResponse::error(
                    "The vessel is booked on another voyage for this time."
                );
            }
        }

        // Check dates if only updating endDate.
        if (isset($validatedData['endDate']) && ! isset($validatedData['startDate'])) {

            // Ensure request endDate is later than startDate.
            if ($validatedData['endDate'] < $voyage['startDate']) {
                return ApiResponse::error(
                    "The voyage end date cannot be set earlier than the voyage start date."
                );
            }

            // Ensure request endDate does not conflict with another voyage for the vessel.
            $vesselIsBooked = VesselVoyage::where(
                'vesselId', $voyage['vesselId']
            )->where('id', '!=', $id)
             ->whereDate('startDate', '<=', $validatedData['endDate'])
             ->whereDate('endDate', '>=', $validatedData['endDate'])
             ->exists();

            if ($vesselIsBooked) {
                return ApiResponse::error(
                    "The vessel is booked on another voyage for this time."
                );
            }
        }

        // Check dates if updating startDate AND endDate.
        if (isset($validatedData['endDate']) && isset($validatedData['startDate'])) {

            // Ensure startDate is earlier than endDate.
            if ($validatedData['startDate'] > $validatedData['endDate']) {
                return ApiResponse::error(
                    "The voyage start date cannot be set later than the voyage end date."
                );
            }
        }

        $voyage->fill($validatedData);
        $voyage->save();

        return ApiResponse::success(
            $voyage->toArray(), 'The voyage was updated.'
        );
    }
}
