<?php

namespace App\Http\Controllers\Cabins\MetaData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VesselCabinAdditionals;
use App\Helpers\ApiResponse;

class ListVesselCabinAdditionals extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $vesselCabinAdditionalsFromDb = VesselCabinAdditionals::get();

        if ($vesselCabinAdditionalsFromDb->isEmpty()) {
          return ApiResponse::error('No cabin information found');
        }

        return ApiResponse::success($vesselCabinAdditionalsFromDb->toArray());
    }
}
