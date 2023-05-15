<?php

namespace App\Http\Controllers\Vessels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vessel;
use App\Helpers\ApiResponse;

class ListVessels extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $vesselsFromDb = Vessel::with(
            'cabins',
            'cabins.additionals',
            'crew_cabins'
        )->get();

        if ($vesselsFromDb->isEmpty()) {
          return ApiResponse::error('No vessels found');
        }

        return ApiResponse::success($vesselsFromDb->toArray());
    }
}
