<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    /**
     * Get all activity logs
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator;

    /**
     * Find activity log by ID
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Create new activity log
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Get logs by user
     *
     * @param int $userId
     * @param array $filters
     * @return Collection
     */
    public function getByUser(int $userId, array $filters = []): Collection;

    /**
     * Get logs by action type
     *
     * @param string $action
     * @param array $filters
     * @return Collection
     */
    public function getByAction(string $action, array $filters = []): Collection;

    /**
     * Get logs by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $filters
     * @return Collection
     */
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection;

    /**
     * Get recent activity
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Clear old logs
     *
     * @param int $daysToKeep
     * @return int Number of deleted logs
     */
    public function clearOldLogs(int $daysToKeep = 30): int;
}
