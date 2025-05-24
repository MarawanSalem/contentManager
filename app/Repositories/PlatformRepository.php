<?php

namespace App\Repositories;

use App\Models\Platform;
use App\Repositories\Interfaces\PlatformRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlatformRepository implements PlatformRepositoryInterface
{
    protected $model;

    public function __construct(Platform $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Platform
    {
        return $this->model->find($id);
    }

    public function create(array $data): Platform
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function getActivePlatformsForUser(int $userId): Collection
    {
        return $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();
    }

    public function togglePlatformForUser(int $userId, int $platformId, bool $active): void
    {
        $platform = $this->find($platformId);

        if ($active) {
            $platform->users()->attach($userId);
        } else {
            $platform->users()->detach($userId);
        }
    }

    public function getActivePlatforms(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function togglePlatformStatus(int $id): bool
    {
        $platform = $this->find($id);
        if ($platform) {
            $platform->is_active = !$platform->is_active;
            return $platform->save();
        }
        return false;
    }

    public function getPlatformsByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }
}
