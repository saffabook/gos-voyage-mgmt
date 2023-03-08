<?php

namespace App\Http\Controllers\Vessels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vessel;
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

        if (!$vessel) {
            return ApiResponse::error('Vessel not found');
        }

        $vessel->delete();

        return ApiResponse::success($vessel, 'Vessel deleted successfully');
    }
}
