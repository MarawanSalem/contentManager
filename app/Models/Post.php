<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'content',
        'scheduled_time',
        'status',
        'image_path',
        'image_url',
        'image_metadata'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'image_metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class)
            ->withPivot(['platform_status', 'platform_error', 'published_at'])
            ->withTimestamps();
    }

    public function hasImage(): bool
    {
        return !empty($this->image_path);
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function deleteImage(): bool
    {
        if ($this->hasImage() && Storage::disk('public')->exists($this->image_path)) {
            Storage::disk('public')->delete($this->image_path);
            $this->update([
                'image_path' => null,
                'image_url' => null,
                'image_metadata' => null
            ]);
            return true;
        }
        return false;
    }
}
