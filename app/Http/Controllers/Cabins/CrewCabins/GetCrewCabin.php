<?php

namespace App\Http\Controllers\Cabins\CrewCabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrewCabin;
use App\Helpers\ApiResponse;

class GetCrewCabin extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $crewCabin = CrewCabin::with('vessel')->find($id);

        if (empty($crewCabin)) {
            return ApiResponse::error('Cabin not found');
        }

        return ApiResponse::success($crewCabin->toArray());
    }
}
