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
     * @group Generated Posts
     *
     * List all generated posts for the authenticated user.
     *
     * @response 200 {
     *     "data": [
     *         {
     *             "id": 1,
     *             "raw_content_id": 1,
     *             "hook_propose": "Did you know AI can write your tweets?",
     *             "body_points": ["AI saves time", "AI maintains consistency"],
     *             "technical_readability_score": 75,
     *             "suggested_hashtags": ["#AI", "#TwitterTips"],
     *             "tone_compliance_justification": "Matches professional yet casual tone",
     *             "status": "draft",
     *             "created_at": "08/07/2026 14:30",
     *             "updated_at": "08/07/2026 14:30"
     *         }
     *     ]
     * }
     * @responseField data.array Object[] List of generated posts.
     *
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
     * @group Generated Posts
     *
     * Get a single generated post by ID.
     *
     * @urlParam generatedPost int required The generated post ID. Example: 1
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "raw_content_id": 1,
     *         "hook_propose": "Did you know AI can write your tweets?",
     *         "body_points": ["AI saves time", "AI maintains consistency"],
     *         "technical_readability_score": 75,
     *         "suggested_hashtags": ["#AI", "#TwitterTips"],
     *         "tone_compliance_justification": "Matches professional yet casual tone",
     *         "status": "draft",
     *         "created_at": "08/07/2026 14:30",
     *         "updated_at": "08/07/2026 14:30"
     *     }
     * }
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     *
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
     * @group Generated Posts
     *
     * Update the status of a generated post.
     *
     * @urlParam generatedPost int required The generated post ID. Example: 1
     *
     * @bodyParam status string required The new status. Must be one of: draft, archived, posted. Example: posted
     *
     * @response 200 {
     *     "message": "Post status updated successfully.",
     *     "data": {
     *         "id": 1,
     *         "raw_content_id": 1,
     *         "hook_propose": "Did you know AI can write your tweets?",
     *         "body_points": ["AI saves time", "AI maintains consistency"],
     *         "technical_readability_score": 75,
     *         "suggested_hashtags": ["#AI", "#TwitterTips"],
     *         "tone_compliance_justification": "Matches professional yet casual tone",
     *         "status": "posted",
     *         "created_at": "08/07/2026 14:30",
     *         "updated_at": "08/07/2026 15:00"
     *     }
     * }
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     *
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "status": ["The status must be one of: draft, archived, posted."]
     *     }
     * }
     *
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