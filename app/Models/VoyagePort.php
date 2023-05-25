<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoyagePort extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'directions',
        'longitude',
        'latitude',
        'addressId',
        'companyId'
    ];

    /**
     * Get all of the voyageEmbarkPorts for the VesselVoyage
     *
     * @return HasMany
     */
    public function voyageEmbarkPorts()
    {
        return $this->hasMany(VesselVoyage::class, 'embarkPortId');
    }

    /**
     * Get all of the voyageDisembarkPorts for the VesselVoyage
     *
     * @return HasMany
     */
    public function voyageDisembarkPorts()
    {
        return $this->hasMany(VesselVoyage::class, 'disembarkPortId');
    }
}
