<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\VoyagePort;

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
        $companyId = 0;

        $validatedData = Validator::make($request->all(), [
            'id'          => 'required|integer|exists:voyage_ports,id',
            'title'       => 'required|string|unique:voyage_ports|max:255'.$request->id,
            'description' => 'string|between:30,600',
            'directions'  => 'string|max:255'
        ], [
            'title.unique' => 'A port with that name already exists.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $port = VoyagePort::where('companyId', $companyId)
            ->find($request->input('id'));

        $port->fill($validatedData->validated());

        $port->save();

        return ApiResponse::success($port, 'The voyage port has been updated');
    }
}
