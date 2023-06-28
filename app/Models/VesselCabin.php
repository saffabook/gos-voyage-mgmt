<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VesselCabin extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'max_occupancy',
        'can_be_booked_single',
        'vessel_id',
        'priceId'
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
     * The prices that belong to the VesselCabin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    // public function prices(): BelongsToMany
    // {
    //     return $this->belongsToMany(VoyagePrice::class, 'price_cabin_pivot', 'cabinId', 'priceId');
    // }
}
