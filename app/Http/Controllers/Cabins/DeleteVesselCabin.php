<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;

class DeleteVesselCabin extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $vesselCabin = VesselCabin::find($id);

        if (empty($vesselCabin)) {
            return ApiResponse::error('Cabin not found');
        }

        $vesselCabin->delete();

        return ApiResponse::success($vesselCabin, 'Cabin deleted successfully');
    }
}