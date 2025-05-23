<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PostService
{
    protected $postRepository;
    protected const MAX_DAILY_POSTS = 10;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createPost(array $data, int $userId): ?Post
    {
        if ($this->postRepository->getDailyScheduledCount($userId) >= self::MAX_DAILY_POSTS) {
            throw new \Exception('Daily post limit reached');
        }

        $data['user_id'] = $userId;
        return $this->postRepository->create($data);
    }

    public function updatePost(int $id, array $data, int $userId): ?Post
    {
        $post = $this->postRepository->find($id);

        if (!$post || $post->user_id !== $userId) {
            return null;
        }

        return $this->postRepository->update($id, $data);
    }

    public function deletePost(int $id, int $userId): bool
    {
        $post = $this->postRepository->find($id);

        if (!$post || $post->user_id !== $userId) {
            return false;
        }

        return $this->postRepository->delete($id);
    }

    public function getUserPosts(int $userId, array $filters = []): Collection
    {
        return $this->postRepository->getUserPosts($userId, $filters);
    }

    public function processScheduledPosts(): void
    {
        $posts = $this->postRepository->getScheduledPosts();

        foreach ($posts as $post) {
            try {
                $this->publishPost($post);
            } catch (\Exception $e) {
                Log::error('Failed to publish post: ' . $e->getMessage(), [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function publishPost(Post $post): void
    {
        // Mock publishing process
        foreach ($post->platforms as $platform) {
            try {
                // Simulate platform-specific publishing
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'published',
                    'published_at' => now()
                ]);
            } catch (\Exception $e) {
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'failed',
                    'platform_error' => $e->getMessage()
                ]);
            }
        }

        $post->update(['status' => 'published']);
    }
}
