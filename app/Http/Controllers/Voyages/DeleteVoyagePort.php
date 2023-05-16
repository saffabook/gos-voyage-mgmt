<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;
use App\Models\VesselVoyage;
use Carbon\Carbon;

class DeleteVoyagePort extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($portId, Request $request)
    {
        $port = VoyagePort::where('companyId', $request->companyId)
                          ->where('id', $portId)
                          ->find($portId);

        if (empty($port)) {
            return ApiResponse::error('Port not found');
        }

        if (!$request->input('forceAction')) {
            $today = Carbon::now();

            $portIsActive = VesselVoyage::where('embarkPortId', $portId)
                                        ->orWhere('disembarkPortId', $portId)
                                        ->whereDate('startDate', '<=', $today)
                                        ->whereDate('endDate', '>=', $today)
                                        ->exists();

            if (!empty($portIsActive)) {
                return ApiResponse::error(
                    'Cannot delete. Port is in use.'
                );
            }
        }

        $port->delete();

        return ApiResponse::success('Port deleted successfully');
    }
}
