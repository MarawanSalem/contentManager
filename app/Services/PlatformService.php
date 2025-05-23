<?php

namespace App\Services;

use App\Models\Platform;
use App\Repositories\PlatformRepository;
use Illuminate\Database\Eloquent\Collection;

class PlatformService
{
    protected $platformRepository;

    public function __construct(PlatformRepository $platformRepository)
    {
        $this->platformRepository = $platformRepository;
    }

    public function getActivePlatforms(): Collection
    {
        return $this->platformRepository->getActivePlatforms();
    }

    public function togglePlatformStatus(int $id): bool
    {
        return $this->platformRepository->togglePlatformStatus($id);
    }

    public function getPlatformsByType(string $type): Collection
    {
        return $this->platformRepository->getPlatformsByType($type);
    }

    public function validatePlatformContent(string $type, string $content): bool
    {
        return match ($type) {
            'twitter' => strlen($content) <= 280,
            'instagram' => strlen($content) <= 2200,
            'linkedin' => strlen($content) <= 3000,
            default => true,
        };
    }
}
