<?php

namespace App\Services\Platforms;

use App\Models\Post;
use App\Models\Platform;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService implements PlatformServiceInterface
{
    protected $platform;
    protected $apiKey;
    protected $apiSecret;
    protected $apiEndpoint;

    public function __construct(Platform $platform)
    {
        $this->platform = $platform;
        $this->apiKey = $platform->api_key;
        $this->apiSecret = $platform->api_secret;
        $this->apiEndpoint = $platform->api_endpoint;
    }

    public function publish(Post $post): bool
    {
        try {
            // TODO: Implement actual Instagram API integration
            // For now, we'll just simulate a successful publish
            Log::info('Publishing to Instagram', [
                'post_id' => $post->id,
                'platform' => 'instagram'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to publish to Instagram', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function validateContent(string $content): bool
    {
        return strlen($content) <= 2200;
    }

    public function validateImage(array $imageMetadata): bool
    {
        if (empty($imageMetadata)) {
            return false; // Instagram requires an image
        }

        $maxSize = 8 * 1024 * 1024; // 8MB
        $minWidth = 320;
        $minHeight = 320;
        $maxWidth = 1440;
        $maxHeight = 1800;
        $allowedTypes = ['image/jpeg'];

        $aspectRatio = $imageMetadata['dimensions']['width'] / $imageMetadata['dimensions']['height'];
        $isValidAspectRatio = $aspectRatio >= 0.8 && $aspectRatio <= 1.91;

        return $imageMetadata['size'] <= $maxSize
            && $imageMetadata['dimensions']['width'] >= $minWidth
            && $imageMetadata['dimensions']['width'] <= $maxWidth
            && $imageMetadata['dimensions']['height'] >= $minHeight
            && $imageMetadata['dimensions']['height'] <= $maxHeight
            && in_array($imageMetadata['mime_type'], $allowedTypes)
            && $isValidAspectRatio;
    }

    public function getImageRequirements(): array
    {
        return [
            'max_size' => '8MB',
            'min_dimensions' => '320x320',
            'max_dimensions' => '1440x1800',
            'allowed_types' => ['JPEG'],
            'aspect_ratio' => '0.8:1 to 1.91:1',
            'max_files' => 1
        ];
    }
}
