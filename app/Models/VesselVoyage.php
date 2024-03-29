<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VesselVoyage extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

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

    /**
     * Get all of the vesselCabins for the VesselVoyage
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vesselCabins(): HasMany
    {
        return $this->hasMany(VesselCabin::class, 'vessel_id', 'vesselId', );
    }

    /**
     * Get the vessel associated with the VesselVoyage
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vessel(): HasOne
    {
        return $this->hasOne(Vessel::class, 'id', 'vesselId');
    }

    /**
     * Get all of the prices for the VesselVoyage
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(VoyagePrice::class, 'voyageId');
    }
}
