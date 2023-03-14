<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewCabin extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'max_occupancy',
        'vessel_id'
    ];

    /**
     * Get the vessel that owns the CrewCabin
     *
     * @return BelongsTo
     */
    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }
}
