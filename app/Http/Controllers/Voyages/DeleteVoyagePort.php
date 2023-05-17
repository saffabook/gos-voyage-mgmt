<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;
use App\Models\VesselVoyage;

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

        $voyagesWithActivePort = VesselVoyage::where('embarkPortId', $portId)
                                             ->orWhere('disembarkPortId', $portId)
                                             ->get();

        if (!empty($voyagesWithActivePort->toArray()) && !$request->input('forceAction')) {
            return ApiResponse::error(
                'Cannot delete. Port is in use.'
            );
        }

        $port->delete();

        if (!empty($voyagesWithActivePort)) {
            foreach ($voyagesWithActivePort as $voyage) {

                $isUpdated = false;

                if ($voyage->embarkPortId == $portId) {
                    $voyage->embarkPortId = null;
                    $isUpdated = true;
                }

                if ($voyage->disembarkPortId == $portId) {
                    $voyage->disembarkPortId = null;
                    $isUpdated = true;
                }

                if ($isUpdated) {
                    $voyage->save();
                }
            }
        }

        return ApiResponse::success('Port deleted successfully');
    }
}
