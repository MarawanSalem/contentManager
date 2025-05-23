<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostRepository extends BaseRepository
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getScheduledPosts(): Collection
    {
        return $this->model
            ->where('status', 'scheduled')
            ->where('scheduled_time', '<=', now())
            ->get();
    }

    public function getUserPosts(int $userId, array $filters = []): Collection
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

        return $query->get();
    }

    public function getDailyScheduledCount(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'scheduled')
            ->whereDate('scheduled_time', today())
            ->count();
    }
}
