<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use App\Services\Platforms\PlatformServiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PostService
{
    protected $postRepository;
    protected $imageService;
    protected const MAX_DAILY_POSTS = 10;

    public function __construct(
        PostRepositoryInterface $postRepository,
        ImageService $imageService
    ) {
        $this->postRepository = $postRepository;
        $this->imageService = $imageService;
    }

    public function getAllPosts(array $filters = []): LengthAwarePaginator
    {
        return $this->postRepository->all($filters);
    }

    public function getUserPosts(array $filters = []): LengthAwarePaginator
    {
        return $this->postRepository->getPostsByUser(Auth::id(), $filters);
    }

    public function createPost(array $data): array
    {
        try {
            // Check daily post limit
            if ($this->exceedsDailyLimit()) {
                return [
                    'success' => false,
                    'message' => 'Daily post limit exceeded (10 posts per day)'
                ];
            }

            // Validate platform requirements
            if (!$this->validatePlatformRequirements($data)) {
                return [
                    'success' => false,
                    'message' => 'Post content does not meet platform requirements'
                ];
            }

            // Create post
            $post = $this->postRepository->create([
                'title' => $data['title'],
                'content' => $data['content'],
                'scheduled_time' => $data['scheduled_time'],
                'status' => $data['status'] ?? 'draft',
                'user_id' => Auth::id(),
                'image_path' => $data['image_path'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'image_metadata' => $data['image_metadata'] ?? null,
            ]);

            // Attach platforms
            if (isset($data['platforms'])) {
                $this->postRepository->attachPlatforms($post->id, $data['platforms']);
            }

            return [
                'success' => true,
                'data' => $post
            ];
        } catch (\Exception $e) {
            Log::error('Post creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create post'
            ];
        }
    }

    public function updatePost(int $id, array $data): array
    {
        try {
            $post = $this->postRepository->find($id);

            if (!$post || $post->user_id !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'Post not found or unauthorized'
                ];
            }

            // Validate platform requirements
            if (!$this->validatePlatformRequirements($data)) {
                return [
                    'success' => false,
                    'message' => 'Post content does not meet platform requirements'
                ];
            }

            // Update post
            $this->postRepository->update($id, [
                'title' => $data['title'] ?? $post->title,
                'content' => $data['content'] ?? $post->content,
                'scheduled_time' => $data['scheduled_time'] ?? $post->scheduled_time,
                'status' => $data['status'] ?? $post->status,
                'image_path' => $data['image_path'] ?? $post->image_path,
                'image_url' => $data['image_url'] ?? $post->image_url,
                'image_metadata' => $data['image_metadata'] ?? $post->image_metadata,
            ]);

            // Update platforms if provided
            if (isset($data['platforms'])) {
                $this->postRepository->detachPlatforms($id, $post->platforms->pluck('id')->toArray());
                $this->postRepository->attachPlatforms($id, $data['platforms']);
            }

            return [
                'success' => true,
                'data' => $this->postRepository->find($id)
            ];
        } catch (\Exception $e) {
            Log::error('Post update failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update post'
            ];
        }
    }

    public function deletePost(int $id): array
    {
        try {
            $post = $this->postRepository->find($id);

            if (!$post || $post->user_id !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'Post not found or unauthorized'
                ];
            }

            $this->postRepository->delete($id);

            return [
                'success' => true,
                'message' => 'Post deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Post deletion failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete post'
            ];
        }
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

    protected function validateImageForPlatforms(array $imageData, array $platformIds): bool
    {
        if (empty($platformIds)) {
            return true;
        }

        foreach ($platformIds as $platformId) {
            $platform = \App\Models\Platform::find($platformId);
            if (!$platform) {
                continue;
            }

            $platformService = PlatformServiceFactory::make($platform);
            if (!$platformService->validateImage($imageData)) {
                return false;
            }
        }

        return true;
    }

    protected function publishPost(Post $post): void
    {
        foreach ($post->platforms as $platform) {
            try {
                $platformService = PlatformServiceFactory::make($platform);

                if ($platformService->validateContent($post->content) &&
                    $platformService->validateImage($post->image_metadata ?? [])) {
                    $success = $platformService->publish($post);

                    $post->platforms()->updateExistingPivot($platform->id, [
                        'platform_status' => $success ? 'published' : 'failed',
                        'platform_error' => $success ? null : 'Failed to publish',
                        'published_at' => $success ? now() : null
                    ]);
                } else {
                    $post->platforms()->updateExistingPivot($platform->id, [
                        'platform_status' => 'failed',
                        'platform_error' => 'Content or image validation failed',
                        'published_at' => null
                    ]);
                }
            } catch (\Exception $e) {
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'failed',
                    'platform_error' => $e->getMessage(),
                    'published_at' => null
                ]);
            }
        }

        // Update post status based on platform statuses
        $allPublished = $post->platforms->every(function ($platform) {
            return $platform->pivot->platform_status === 'published';
        });

        $anyFailed = $post->platforms->contains(function ($platform) {
            return $platform->pivot->platform_status === 'failed';
        });

        $post->update([
            'status' => $allPublished ? 'published' : ($anyFailed ? 'failed' : 'scheduled')
        ]);
    }

    protected function exceedsDailyLimit(): bool
    {
        $todayPosts = $this->postRepository->getPostsByUser(Auth::id(), [
            'date_from' => now()->startOfDay(),
            'date_to' => now()->endOfDay()
        ]);

        return $todayPosts->total() >= 10;
    }

    protected function validatePlatformRequirements(array $data): bool
    {
        // Implement platform-specific validation rules
        // For example, Twitter's 280 character limit
        if (isset($data['platforms']) && in_array(1, $data['platforms'])) { // Assuming 1 is Twitter
            if (strlen($data['content']) > 280) {
                return false;
            }
        }

        return true;
    }
}
