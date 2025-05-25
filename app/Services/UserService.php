<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;
    protected $activityLogRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ActivityLogRepositoryInterface $activityLogRepository
    ) {
        $this->userRepository = $userRepository;
        $this->activityLogRepository = $activityLogRepository;
    }

    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->all($filters);
    }

    public function createUser(array $data): array
    {
        try {
            $user = $this->userRepository->create($data);

            $this->logActivity('user.created', 'User registered', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
        }
    }

    public function updateUser(int $id, array $data): array
    {
        try {
            $user = $this->userRepository->find($id);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            $this->userRepository->update($id, $data);

            $this->logActivity('user.updated', 'User profile updated', [
                'user_id' => $id,
                'changes' => array_keys($data)
            ]);

            return [
                'success' => true,
                'data' => $this->userRepository->find($id)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user'
            ];
        }
    }

    public function deleteUser(int $id): array
    {
        try {
            $user = $this->userRepository->find($id);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            $this->userRepository->delete($id);

            $this->logActivity('user.deleted', 'User deleted', [
                'user_id' => $id,
                'user_email' => $user->email
            ]);

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete user'
            ];
        }
    }

    public function getUserPosts(int $userId, array $filters = []): Collection
    {
        return $this->userRepository->getPosts($userId, $filters);
    }

    public function getUserPlatforms(int $userId): Collection
    {
        return $this->userRepository->getPlatforms($userId);
    }

    public function getDailyPostCount(int $userId): int
    {
        return $this->userRepository->getDailyPostCount($userId);
    }

    public function validateDailyPostLimit(int $userId): bool
    {
        return $this->getDailyPostCount($userId) < 10;
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
}
