<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    /**
     * Get all of the comments for the Post
     *
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }
}
