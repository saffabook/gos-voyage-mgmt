<?php

namespace App\Http\Controllers\Vessels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\CrewCabin;
use App\Helpers\ApiResponse;

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

        $vessel->delete();
        VesselCabin::where('vessel_id', $id)->delete();
        CrewCabin::where('vessel_id', $id)->delete();

        return ApiResponse::success($vessel, 'Vessel deleted successfully');
    }
}
