<?php

namespace App\Services;

use App\Repositories\Interfaces\PlatformRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlatformService
{
    protected $platformRepository;

    public function __construct(PlatformRepositoryInterface $platformRepository)
    {
        $this->platformRepository = $platformRepository;
    }

    public function getAllPlatforms(): Collection
    {
        return $this->platformRepository->all();
    }

    public function getActivePlatforms(): Collection
    {
        return $this->platformRepository->getActivePlatformsForUser(Auth::id());
    }

    public function togglePlatform(int $platformId, bool $active): array
    {
        try {
            $this->platformRepository->togglePlatformForUser(Auth::id(), $platformId, $active);

            return [
                'success' => true,
                'message' => $active ? 'Platform activated' : 'Platform deactivated'
            ];
        } catch (\Exception $e) {
            Log::error('Platform toggle failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to toggle platform'
            ];
        }
    }

    public function validatePostForPlatform(int $platformId, array $postData): array
    {
        $platform = $this->platformRepository->find($platformId);

        if (!$platform) {
            return [
                'valid' => false,
                'message' => 'Platform not found'
            ];
        }

        // Platform-specific validation rules
        switch ($platform->type) {
            case 'twitter':
                if (strlen($postData['content']) > 280) {
                    return [
                        'valid' => false,
                        'message' => 'Twitter posts must be 280 characters or less'
                    ];
                }
                break;

            case 'instagram':
                if (empty($postData['image_path'])) {
                    return [
                        'valid' => false,
                        'message' => 'Instagram posts must include an image'
                    ];
                }
                break;

            case 'linkedin':
                if (strlen($postData['content']) > 3000) {
                    return [
                        'valid' => false,
                        'message' => 'LinkedIn posts must be 3000 characters or less'
                    ];
                }
                break;
        }

        return [
            'valid' => true,
            'message' => 'Post is valid for this platform'
        ];
    }
}
