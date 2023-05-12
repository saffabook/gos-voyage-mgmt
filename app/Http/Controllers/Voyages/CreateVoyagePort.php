<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;

class CreateVoyagePort extends Controller
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
            'title'       => 'required|string|unique:voyage_ports|max:255',
            'description' => 'string|between:30,600',
            'directions'  => 'string|max:255'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $companyId;

        // This is dummy data until we create addresses table
        $validatedData['addressId'] = 42;

        $port = VoyagePort::create($validatedData);

        return ApiResponse::success($port->toArray(), 'The port was created');
    }
}
