<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoyageCabinPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cabinId',
        'voyageId',
        'currency',
        'priceMinor',
        'discountedPriceMinor',
        'companyId'
    ];

    /**
     * Get the cabin that owns the VoyageCabinPrice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cabin(): BelongsTo
    {
        return $this->belongsTo(VesselCabin::class, 'cabinId');
    }

    /**
     * Get the voyage that owns the VoyageCabinPrice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voyage(): BelongsTo
    {
        return $this->belongsTo(VesselVoyage::class, 'voyageId');
    }
}
