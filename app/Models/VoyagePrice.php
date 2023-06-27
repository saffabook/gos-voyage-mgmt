<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VoyagePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'voyageId',
        'currency',
        'priceMinor',
        'discountedPriceMinor',
        'companyId'
    ];

    /**
     * Get the voyage that owns the VoyagePrice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voyage(): BelongsTo
    {
        return $this->belongsTo(VesselVoyage::class, 'voyageId');
    }

    /**
     * The cabins that belong to the VoyagePrice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cabins(): BelongsToMany
    {
        return $this->belongsToMany(VesselCabin::class, 'price_cabin_pivot', 'priceId', 'cabinId');
    }
}
