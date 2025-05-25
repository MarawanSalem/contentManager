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

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getActive(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $platform = $this->find($id);
        if (!$platform) {
            return false;
        }
        return $platform->update($data);
    }

    public function delete(int $id): bool
    {
        $platform = $this->find($id);
        if (!$platform) {
            return false;
        }
        return $platform->delete();
    }

    public function toggleStatus(int $id): bool
    {
        $platform = $this->find($id);
        if (!$platform) {
            return false;
        }
        return $platform->update([
            'status' => $platform->status === 'active' ? 'inactive' : 'active'
        ]);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->get();
    }
}
