<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePostStatusRequest;
use App\Http\Resources\GeneratedPostResource;
use App\Models\GeneratedPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GeneratedPostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the authenticated user's generated posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = GeneratedPost::query()
            ->whereHas('rawContent', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with(['rawContent'])
            ->latest()
            ->get();

        return GeneratedPostResource::collection($posts);
    }

    /**
     * Display the specified generated post.
     */
    public function show(Request $request, GeneratedPost $generatedPost): JsonResponse
    {
        $this->authorize('view', $generatedPost);

        $generatedPost->load(['rawContent']);

        return response()->json([
            'data' => GeneratedPostResource::make($generatedPost),
        ], 200);
    }

    /**
     * Update the status of the specified generated post.
     */
    public function updateStatus(UpdatePostStatusRequest $request, GeneratedPost $generatedPost): JsonResponse
    {
        $this->authorize('update', $generatedPost);

        $generatedPost->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Post status updated successfully.',
            'data'    => GeneratedPostResource::make($generatedPost),
        ], 200);
    }
}