<?php

namespace App\Repositories\Interfaces;

interface PlatformRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getActivePlatformsForUser(int $userId);
    public function togglePlatformForUser(int $userId, int $platformId, bool $active);
}
