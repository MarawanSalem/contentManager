<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Get all users
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function all(array $filters = []): LengthAwarePaginator;

    /**
     * Find user by ID
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Find user by email
     *
     * @param string $email
     * @return mixed
     */
    public function findByEmail(string $email);

    /**
     * Create new user
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get user's posts
     *
     * @param int $userId
     * @param array $filters
     * @return Collection
     */
    public function getPosts(int $userId, array $filters = []): Collection;

    /**
     * Get user's platforms
     *
     * @param int $userId
     * @return Collection
     */
    public function getPlatforms(int $userId): Collection;

    /**
     * Get user's daily post count
     *
     * @param int $userId
     * @return int
     */
    public function getDailyPostCount(int $userId): int;
}
