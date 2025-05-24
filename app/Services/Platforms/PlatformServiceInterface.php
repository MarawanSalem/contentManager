<?php

namespace App\Services\Platforms;

use App\Models\Post;

interface PlatformServiceInterface
{
    public function publish(Post $post): bool;
    public function validateContent(string $content): bool;
    public function validateImage(array $imageMetadata): bool;
    public function getImageRequirements(): array;
}
