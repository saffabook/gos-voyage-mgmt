<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;

class DeleteVoyagePort extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id, Request $request)
    {
        $port = VoyagePort::where('companyId', $request->companyId)
                          ->where('id', $id)
                          ->delete();

        if (empty($port)) {
            return ApiResponse::error('Port not found');
        }

        return ApiResponse::success('Port deleted successfully');
    }
}
