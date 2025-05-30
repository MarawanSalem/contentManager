<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlatformRepositoryInterface
{
    /**
     * Get all platforms
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator;

    /**
     * Get active platforms
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Find platform by ID
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Create new platform
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update platform
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete platform
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Toggle platform status
     *
     * @param int $id
     * @return bool
     */
    public function toggleStatus(int $id): bool;

    /**
     * Get platforms by user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUser(int $userId): Collection;
}
