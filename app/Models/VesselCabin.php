<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VesselCabin extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'max_occupancy',
        'can_be_booked_single',
        'vessel_id'
    ];

    /**
     * Get the vessel that owns the VesselCabin
     *
     * @return BelongsTo
     */
    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * Get all of the additionals for the VesselCabin
     *
     * @return HasMany
     */
    public function additionals()
    {
        return $this->hasMany(VesselCabinAdditionals::class, 'cabin_id');
    }

    /**
     * Get the price associated with the VesselCabin
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function price(): HasOne
    {
        return $this->hasOne(VoyageCabinPrice::class, 'cabinId');
    }
}
