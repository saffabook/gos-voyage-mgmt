<?php

namespace App\Services\Voyages;

use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Illuminate\Support\Facades\Validator;

class CreateVesselVoyageService
{
    public static function validateData($request)
    {
        $validatedData = Validator::make($request->all(), [
            'title'              => 'required|string|max:255',
            'description'        => 'string|between:30,600',
            'vesselId'           => 'exists:vessels,id,companyId,' . $request['companyId'],
            'voyageType'         => 'in:ROUNDTRIP,ONEWAY,DAYTRIP',
            'isPassportRequired' => 'boolean',
            'embarkPortId'       => 'required|integer',
            'disembarkPortId'    => 'required|integer',
            'startDate'          => 'required|date_format:Y-m-d|before:endDate',
            'startTime'          => 'required|date_format:H:i',
            'endDate'            => 'required|date_format:Y-m-d',
            'endTime'            => 'required|date_format:H:i',
            'companyId'          => 'required|integer'
        ]);

        if ($validatedData->fails()) {
            return ['error' => $validatedData->messages()];
        }
        return $validatedData->validated();
    }

    public static function checkPortsExist($validatedData)
    {
        // Check requested ports validity.
        if (isset($validatedData['embarkPortId'])) {
            $portIds[] = $validatedData['embarkPortId'];
        }

        if (isset($validatedData['disembarkPortId'])) {
            $portIds[] = $validatedData['disembarkPortId'];
        }

        if (isset($portIds) && ! empty($portIds)) {
            $portIds = array_unique($portIds);

            $portsFound = VoyagePort::whereIn('id', $portIds)
                ->where('companyId', $validatedData['companyId'])
                ->get();

            if ($portsFound->count() != count($portIds)) {
                return false;
            }
        }

        return true;
    }

    public static function isVesselBooked($validatedData)
    {
        $isVesselBooked = VesselVoyage::where('vesselId', $validatedData['vesselId'])
            ->whereDate('startDate', '<=', $validatedData['endDate'])
            ->whereDate('endDate', '>=', $validatedData['startDate'])
            ->exists();

        if ($isVesselBooked) {
            return true;
        }
    }
}
