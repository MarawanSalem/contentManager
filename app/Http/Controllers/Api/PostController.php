<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);
        $posts = $this->postService->getUserPosts($filters);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scheduled_time' => 'required|date',
            'platforms' => 'required|array',
            'platforms.*' => 'exists:platforms,id',
            'image' => 'nullable|image|max:10240' // 10MB max
        ]);

        $result = $this->postService->createPost($validated);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        $post = $this->postService->getUserPosts(['id' => $id])->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'scheduled_time' => 'sometimes|required|date',
            'platforms' => 'sometimes|required|array',
            'platforms.*' => 'exists:platforms,id',
            'image' => 'nullable|image|max:10240' // 10MB max
        ]);

        $result = $this->postService->updatePost($id, $validated);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->postService->deletePost($id);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }
}
