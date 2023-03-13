<?php

namespace App\Http\Controllers\Cabins\CrewCabins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CrewCabin;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

class UpdateCrewCabin extends Controller
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
            'id' => 'required|integer|exists:crew_cabins,id,vessel_id,'.$request->vessel_id,
            'title' => [
                'string',
                'max:255',
                Rule::unique('crew_cabins')
                    ->where('vessel_id', $request->vessel_id),
            ],
            'description' => 'string|max:255',
            'vessel_id'   => 'required|integer|exists:vessels,id'
        ], [
            'title.unique'     => 'This vessel already has a cabin with that name.',
            'vessel_id.exists' => 'This vessel does not exist.'
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error($validatedData->messages());
        }

        $validatedData = $validatedData->validated();
        unset($validatedData['vessel_id']);
        $crewCabin = CrewCabin::find($request->input('id'));
        $crewCabin->fill($validatedData);
        $crewCabin->save();

        return ApiResponse::success($crewCabin, 'The cabin was updated');
    }
}
