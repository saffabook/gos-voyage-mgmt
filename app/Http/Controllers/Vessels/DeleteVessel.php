<?php

namespace App\Http\Controllers\Vessels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\CrewCabin;
use App\Helpers\ApiResponse;
use App\Models\VesselCabinAdditionals;

class DeleteVessel extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $vessel = Vessel::find($id);

        if (empty($vessel)) {
            return ApiResponse::error('Vessel not found');
        }

        $vesselCabin = VesselCabin::where('vessel_id', $id)->get();
        foreach ($vesselCabin as $key => $cabin) {
            VesselCabinAdditionals::where('cabin_id', $cabin->id)->delete();
            $cabin->delete();
        }
        $vessel->delete();

        return ApiResponse::success('Vessel deleted successfully');
    }
}
