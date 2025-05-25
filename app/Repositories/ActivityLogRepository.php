<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use App\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    protected $model;

    public function __construct(ActivityLog $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getByUser(int $userId, array $filters = []): Collection
    {
        $query = $this->model->where('user_id', $userId);

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getByAction(string $action, array $filters = []): Collection
    {
        $query = $this->model->where('action', $action);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = $this->model->whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    public function clearOldLogs(int $daysToKeep = 30): int
    {
        $date = now()->subDays($daysToKeep);
        return $this->model->where('created_at', '<', $date)->delete();
    }
}
