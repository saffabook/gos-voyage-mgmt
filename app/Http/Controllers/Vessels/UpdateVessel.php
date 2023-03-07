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
            'name' => 'string|max:30|unique:vessels,name,'.$request->id, // Name is unique compared to all but itself
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

        // TODO: Study the next three lines - write an explanation for review

        // Retrieve model based on id from input in the request
        $vessel = Vessel::find($request->input('id'));

        // Populate model with attributes according to the validated data
        $vessel->fill($validatedData->validated());

        // Update the record in the database
        // (cf. https://laravel.com/docs/10.x/eloquent#updates)
        $vessel->save();

        return ApiResponse::success($vessel, 'The vessel was updated');
    }
}
