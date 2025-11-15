<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'from_id',
        'to_id',
    ];

    /**
     * Get the user that is following (from_id).
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Get the user that is being followed (to_id).
     */
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_id');
    }
}
