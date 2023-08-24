<?php

namespace App\Http\Controllers\Voyages;

use App\Helpers\ApiResponse;
use App\Helpers\GenerateVoyageId;
use App\Http\Controllers\Controller;
use App\Models\VesselVoyage;
use App\Services\Voyages\CreateVesselVoyageService;
use Illuminate\Http\Request;

class CreateVesselVoyage extends Controller
{
    /**
     * Create voyage primary data after checking the user/company is authorized
     * to use the requested vessel and ports, and that voyage dates make sense
     * chronologically and do not conflict with another voyage for the same vessel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Helpers\ApiResponse
     */
    public function __invoke(Request $request)
    {
        // Validate request data.
        $validatedData = CreateVesselVoyageService::validateData($request);

        if (isset($validatedData['error'])) {
            return ApiResponse::error($validatedData['error']);
        }

        // Check ports exist.
        $portsExist = CreateVesselVoyageService::checkPortsExist($validatedData);

        if (! $portsExist) {
            return ApiResponse::error('The requested ports are invalid.');
        }

        // Check vessel availability.
        $vesselIsBooked = CreateVesselVoyageService::isVesselBooked($validatedData);

        if ($vesselIsBooked) {
            return ApiResponse::error(
                'The requested vessel is already booked for this time.'
            );
        }

        // Create voyage ref.
        $validatedData['voyageReferenceNumber'] = GenerateVoyageId::execute(
            $request->input('companyId')
        );

        $voyage = VesselVoyage::create($validatedData);

        return ApiResponse::success(
            $voyage->toArray(), 'The voyage was created.'
        );
    }
}
