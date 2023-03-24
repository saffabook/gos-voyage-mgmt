<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Get the embarkPort associated with the VesselVoyage
     *
     * @return HasOne
     */
    public function embarkPort(): HasOne
    {
        return $this->hasOne(VoyagePort::class, 'id', 'embarkPortId');
    }

    /**
     * Get the disembarkPort associated with the VesselVoyage
     *
     * @return HasOne
     */
    public function disembarkPort(): HasOne
    {
        return $this->hasOne(VoyagePort::class, 'id', 'disembarkPortId');
    }
}
