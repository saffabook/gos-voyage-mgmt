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
    public function __invoke(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'id'    => 'required|integer|exists:voyage_ports,id',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('voyage_ports')
                    ->where('companyId', $request->companyId)
                    ->ignore($request->id)
            ],
            'description' => 'string|between:30,600',
            'directions'  => 'string|max:255'
        ], [
            'title.unique' => 'You have already created a port with that name.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $port = VoyagePort::where('companyId', $request->input('companyId'))
                          ->find($request->input('id'));

        if (empty($port)) {
            return ApiResponse::error('Port not found.');
        }

        $port->fill($validatedData->validated());

        $port->save();

        return ApiResponse::success($port, 'The port has been updated.');
    }
}
