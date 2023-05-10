<?php

namespace App\Http\Controllers\Voyages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VoyagePort;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

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
        $validatedData = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('voyage_ports')
                    ->where('companyId', $request->companyId),
            ],
            'description' => 'string|between:30,600',
            'directions'  => 'string|max:255'
        ], [
            'title.unique' => 'You have already created a port with that name.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();

        $validatedData['companyId'] = $request->input('companyId');

        // This is dummy data until we create addresses table
        $validatedData['addressId'] = 42;

        $port = VoyagePort::create($validatedData);

        return ApiResponse::success($port, 'The port was created');
    }
}
