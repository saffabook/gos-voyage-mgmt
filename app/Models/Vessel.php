<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'vessel_type', 
        'year_built', 
        'length_overall', 
        'length_on_deck', 
        'description', 
        'company_id'
    ];

    /**
     * Get all of the cabins for the Vessel
     *
     * @return HasMany
     */
    public function cabins()
    {
        return $this->hasMany(VesselCabin::class);
    }
}
