<?php

namespace App\Http\Controllers\Vessels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Vessel;
use App\Helpers\ApiResponse;

class UpdateVessel extends Controller
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
            'id' => 'required|integer|exists:vessels,id',
            'name' => 'string|max:30|unique:vessels,name,'.$request->id,
            'vessel_type' => 'string|max:30',
            'year_built' => 'nullable|date_format:Y-m-d',
            'length_overall' => 'integer',
            'length_on_deck' => 'integer',
            'description' => 'string|between:30,600'
        ], [
            'name.unique' => 'A vessel with that name already exists.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $vessel = Vessel::find($request->input('id'));
        $vessel->fill($validatedData->validated());
        $vessel->save();

        return ApiResponse::success(
            $vessel->toArray(), 'The vessel has been updated'
        );
    }
}
