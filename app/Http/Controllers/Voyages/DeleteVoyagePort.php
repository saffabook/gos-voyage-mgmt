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
    public function __invoke($id)
    {
        $port = VoyagePort::find($id);

        if (empty($port)) {
            return ApiResponse::error('Port not found');
        }

        $port->delete();

        return ApiResponse::success($port, 'Port deleted successfully');
    }
}
