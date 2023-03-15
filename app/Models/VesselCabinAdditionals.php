<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselCabinAdditionals extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cabin_id'
    ];

    /**
     * Get the cabin that owns the VesselCabinAdditionals
     *
     * @return BelongsTo
     */
    public function cabin()
    {
        return $this->belongsTo(VesselCabin::class);
    }
}
