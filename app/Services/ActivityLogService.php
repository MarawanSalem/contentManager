<?php

namespace App\Services;

use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    protected $activityLogRepository;

    public function __construct(ActivityLogRepositoryInterface $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    public function getAllLogs(array $filters = []): LengthAwarePaginator
    {
        return $this->activityLogRepository->all($filters);
    }

    public function getLogsByUser(int $userId, array $filters = []): Collection
    {
        return $this->activityLogRepository->getByUser($userId, $filters);
    }

    public function getLogsByAction(string $action, array $filters = []): Collection
    {
        return $this->activityLogRepository->getByAction($action, $filters);
    }

    public function getLogsByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        return $this->activityLogRepository->getByDateRange($startDate, $endDate, $filters);
    }

    public function getRecentLogs(int $limit = 10): Collection
    {
        return $this->activityLogRepository->getRecent($limit);
    }

    public function createLog(string $action, string $description, array $metadata = []): void
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

    public function clearOldLogs(int $daysToKeep = 30): int
    {
        return $this->activityLogRepository->clearOldLogs($daysToKeep);
    }

    public function getActivityStats(): array
    {
        $logs = $this->activityLogRepository->all();

        return [
            'total_actions' => $logs->total(),
            'actions_by_type' => $logs->groupBy('action')
                ->map(fn($group) => $group->count()),
            'recent_activity' => $this->getRecentLogs(5),
            'user_activity' => $logs->groupBy('user_id')
                ->map(fn($group) => $group->count())
        ];
    }
}
