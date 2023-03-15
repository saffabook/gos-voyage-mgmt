<?php

namespace App\Http\Controllers\Cabins\MetaData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VesselCabinAdditionals;
use App\Helpers\ApiResponse;

class DeleteVesselCabinAdditionals extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        $cabinAdditionals = VesselCabinAdditionals::find($id);

        if (empty($cabinAdditionals)) {
            return ApiResponse::error('Cabin information not found');
        }

        $cabinAdditionals->delete();

        return ApiResponse::success(
            $cabinAdditionals, 'Cabin information deleted successfully'
        );
    }
}
