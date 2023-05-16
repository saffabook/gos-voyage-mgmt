<?php

namespace App\Http\Controllers\Cabins\CrewCabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrewCabin;
use App\Helpers\ApiResponse;

class ListCrewCabins extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $crewCabinsFromDb = CrewCabin::get();

        if ($crewCabinsFromDb->isEmpty()) {
          return ApiResponse::error('No cabins found');
        }

        return ApiResponse::success($crewCabinsFromDb->toArray());
    }
}
