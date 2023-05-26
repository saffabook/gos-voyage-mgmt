<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\VoyagePort;
use Illuminate\Validation\Rule;

class UpdateVoyagePort extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($portId, Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('voyage_ports')
                    ->where('companyId', $request->companyId)
                    ->ignore($request->id)
            ],
            'description' => 'string|between:30,600',
            'directions'  => 'string|max:255',
            'forceAction' => 'sometimes|in:1,true',
        ], [
            'title.unique'   => 'You have already created a port with that name.',
            'forceAction.in' => 'forceAction can only be true'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $port = VoyagePort::where('companyId', $request->companyId)
                          ->with('voyageEmbarkPorts', 'voyageDisembarkPorts')
                          ->find($portId);

        if (empty($port)) {
            return ApiResponse::error('Port not found.');
        }

        if (!isset($validatedData['forceAction']) || !$validatedData['forceAction']) {
            if (!$port->voyageEmbarkPorts->isEmpty() || !$port->voyageDisembarkPorts->isEmpty()) {
                return ApiResponse::error('Cannot update. Port is in use.');
            }
        }

        $port->fill($validatedData);
        $port->save();

        return ApiResponse::success(
            $port->toArray(), 'The port has been updated.'
        );
    }
}
