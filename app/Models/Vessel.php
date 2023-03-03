<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'vessel_type', 'year_built', 'length_overall', 'length_on_deck', 'description', 'company_id'
    ];
}
