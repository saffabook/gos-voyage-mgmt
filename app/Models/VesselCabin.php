<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Get the additionals associated with the VesselCabin
     *
     * @return HasOne
     */
    public function additionals()
    {
        return $this->hasOne(VesselCabinAdditionals::class, 'cabin_id');
    }
}
