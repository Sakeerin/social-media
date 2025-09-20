<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    /**
     * Get the user that owns the like.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that owns the like.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
