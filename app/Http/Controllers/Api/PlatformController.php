<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlatformService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    protected $platformService;

    public function __construct(PlatformService $platformService)
    {
        $this->platformService = $platformService;
    }

    public function index(): JsonResponse
    {
        $platforms = $this->platformService->getAllPlatforms();

        return response()->json([
            'success' => true,
            'data' => $platforms
        ]);
    }

    public function active(): JsonResponse
    {
        $platforms = $this->platformService->getActivePlatforms();

        return response()->json([
            'success' => true,
            'data' => $platforms
        ]);
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'active' => 'required|boolean'
        ]);

        $result = $this->platformService->togglePlatform($id, $validated['active']);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function validatePost(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'image_path' => 'nullable|string'
        ]);

        $result = $this->platformService->validatePostForPlatform($id, $validated);

        return response()->json($result);
    }
}
