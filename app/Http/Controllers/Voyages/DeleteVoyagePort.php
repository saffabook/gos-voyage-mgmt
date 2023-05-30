<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;

class DeleteVoyagePort extends Controller
{
    /**
     * Private function to help set ports to null on voyages where
     * the port has been deleted
     *
     * @param [Class] $portCollection a relationship subcollection of database call
     * @param [String] $property a target to set the element to be null
     * @return void
     */
    private function setVoyagePortToNull($portCollection, $property)
    {
        if (!$portCollection->isEmpty()) {
            foreach($portCollection as $port) {
                $port->{$property} = null;
                $port->save();
            }
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($portId, Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'forceAction' => 'sometimes|in:1,true',
        ], [
            'forceAction.in' => 'forceAction can only be true'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $port = VoyagePort::where('companyId', $request->companyId)
                          ->with('voyageEmbarkPorts', 'voyageDisembarkPorts')
                          ->where('id', $portId)
                          ->find($portId);

        if (empty($port)) {
            return ApiResponse::error('Port not found');
        }

        if (!isset($validatedData['forceAction']) || !$validatedData['forceAction']) {
            if (!$port->voyageEmbarkPorts->isEmpty() || !$port->voyageDisembarkPorts->isEmpty()) {
                return ApiResponse::error('Cannot delete. Port is in use.');
            }
        }

        $this->setVoyagePortToNull($port->voyageEmbarkPorts, 'embarkPortId');
        $this->setVoyagePortToNull($port->voyageDisembarkPorts, 'disembarkPortId');

        $port->delete();

        return ApiResponse::success('Port deleted successfully');
    }
}
