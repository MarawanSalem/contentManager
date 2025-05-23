<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PostPlatform extends Pivot
{
    protected $table = 'post_platforms';

    protected $fillable = [
        'post_id',
        'platform_id',
        'platform_status',
        'platform_error',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
