<?php

namespace App\Http\Controllers\Cabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VesselCabin;
use App\Helpers\ApiResponse;

class ListVesselCabins extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $vesselCabinsFromDb = VesselCabin::get();

        if ($vesselCabinsFromDb->isEmpty()) {
          return ApiResponse::error('No cabins found');
        }

        return ApiResponse::success($vesselCabinsFromDb);
    }
}
