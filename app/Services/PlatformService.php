<?php

namespace App\Services;

use App\Repositories\Interfaces\PlatformRepositoryInterface;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlatformService
{
    protected $platformRepository;
    protected $activityLogRepository;

    public function __construct(
        PlatformRepositoryInterface $platformRepository,
        ActivityLogRepositoryInterface $activityLogRepository
    ) {
        $this->platformRepository = $platformRepository;
        $this->activityLogRepository = $activityLogRepository;
    }

    public function getAllPlatforms(array $filters = []): LengthAwarePaginator
    {
        return $this->platformRepository->all($filters);
    }

    public function getActivePlatforms(): Collection
    {
        return $this->platformRepository->getActive();
    }

    public function createPlatform(array $data): array
    {
        try {
            $platform = $this->platformRepository->create($data);

            $this->logActivity('platform.created', 'Platform created', [
                'platform_id' => $platform->id,
                'platform_name' => $platform->name
            ]);

            return [
                'success' => true,
                'data' => $platform
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create platform'
            ];
        }
    }

    public function updatePlatform(int $id, array $data): array
    {
        try {
            $platform = $this->platformRepository->find($id);

            if (!$platform) {
                return [
                    'success' => false,
                    'message' => 'Platform not found'
                ];
            }

            $this->platformRepository->update($id, $data);

            $this->logActivity('platform.updated', 'Platform updated', [
                'platform_id' => $id,
                'changes' => $data
            ]);

            return [
                'success' => true,
                'data' => $this->platformRepository->find($id)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update platform'
            ];
        }
    }

    public function deletePlatform(int $id): array
    {
        try {
            $platform = $this->platformRepository->find($id);

            if (!$platform) {
                return [
                    'success' => false,
                    'message' => 'Platform not found'
                ];
            }

            $this->platformRepository->delete($id);

            $this->logActivity('platform.deleted', 'Platform deleted', [
                'platform_id' => $id,
                'platform_name' => $platform->name
            ]);

            return [
                'success' => true,
                'message' => 'Platform deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete platform'
            ];
        }
    }

    public function togglePlatformStatus(int $id): array
    {
        try {
            $platform = $this->platformRepository->find($id);

            if (!$platform) {
                return [
                    'success' => false,
                    'message' => 'Platform not found'
                ];
            }

            $this->platformRepository->toggleStatus($id);

            $this->logActivity('platform.status_toggled', 'Platform status toggled', [
                'platform_id' => $id,
                'platform_name' => $platform->name,
                'new_status' => $platform->status === 'active' ? 'inactive' : 'active'
            ]);

            return [
                'success' => true,
                'data' => $this->platformRepository->find($id)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to toggle platform status'
            ];
        }
    }

    public function getUserPlatforms(int $userId): Collection
    {
        return $this->platformRepository->getByUser($userId);
    }

    protected function logActivity(string $action, string $description, array $metadata = []): void
    {
        $this->activityLogRepository->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
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
