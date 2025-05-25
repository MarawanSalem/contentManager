<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }
        return $user->delete();
    }

    public function getPosts(int $userId, array $filters = []): Collection
    {
        $user = $this->find($userId);
        if (!$user) {
            return collect();
        }

        $query = $user->posts();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('scheduled_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('scheduled_time', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    public function getPlatforms(int $userId): Collection
    {
        $user = $this->find($userId);
        if (!$user) {
            return collect();
        }
        return $user->platforms;
    }

    public function getDailyPostCount(int $userId): int
    {
        return $this->model->find($userId)
            ->posts()
            ->whereDate('created_at', now())
            ->count();
    }
}
