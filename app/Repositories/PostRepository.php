<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    protected $model;

    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('scheduled_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('scheduled_time', '<=', $filters['date_to']);
        }

        return $query->with(['user', 'platforms'])->paginate(10);
    }

    public function find(int $id): ?Post
    {
        return $this->model->with(['user', 'platforms'])->find($id);
    }

    public function create(array $data): Post
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

    public function getScheduledPosts(): Collection
    {
        return $this->model
            ->where('status', 'scheduled')
            ->where('scheduled_time', '<=', now())
            ->with(['user', 'platforms'])
            ->get();
    }

    public function getPostsByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('scheduled_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('scheduled_time', '<=', $filters['date_to']);
        }

        return $query->with(['platforms'])->paginate(10);
    }

    public function attachPlatforms(int $postId, array $platformIds): void
    {
        $post = $this->find($postId);
        $post->platforms()->attach($platformIds);
    }

    public function detachPlatforms(int $postId, array $platformIds): void
    {
        $post = $this->find($postId);
        $post->platforms()->detach($platformIds);
    }
}
