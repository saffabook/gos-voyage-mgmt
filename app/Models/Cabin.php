<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VesselCabin extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'can_be_booked_single', 'vessel_id'];
}
