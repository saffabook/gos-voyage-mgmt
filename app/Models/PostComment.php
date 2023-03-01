<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostComment extends Model
{
    use HasFactory;

    protected $fillable = ['comment', 'post_id'];

    /**
     * Get the post that owns the PostComment
     *
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
