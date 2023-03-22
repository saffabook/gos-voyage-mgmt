<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VesselVoyage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'vesselId',
        'voyageType',
        'voyageReferenceNumber',
        'isPassportRequired',
        'embarkPortId',
        'startDate',
        'startTime',
        'disembarkPortId',
        'endDate',
        'endTime',
        'companyId',
        'voyageStatus'
    ];
}
