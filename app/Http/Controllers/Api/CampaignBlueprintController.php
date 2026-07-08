<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBlueprintRequest;
use App\Http\Requests\Api\UpdateBlueprintRequest;
use App\Http\Resources\CampaignBlueprintResource;
use App\Models\CampaignBlueprint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignBlueprintController extends Controller
{
    use AuthorizesRequests;

    /**
     * @group Campaign Blueprints
     *
     * List all blueprints for the authenticated user.
     *
     * @response 200 {
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Product Launch",
     *             "tone_description": "Professional and enthusiastic",
     *             "max_characters": 280,
     *             "max_hashtags": 3,
     *             "extra_rules": ["Always include a call-to-action"],
     *             "posts_count": 5,
     *             "created_at": "08/07/2026 10:00",
     *             "updated_at": "08/07/2026 12:00"
     *         }
     *     ]
     * }
     * @responseField data.array Object[] List of campaign blueprints.
     *
     * Display a listing of the authenticated user's blueprints.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $blueprints = CampaignBlueprint::query()
            ->where('user_id', $request->user()->id)
            ->with(['rawContents.generatedPost'])
            ->latest()
            ->get();

        return CampaignBlueprintResource::collection($blueprints);
    }

    /**
     * @group Campaign Blueprints
     *
     * Create a new campaign blueprint.
     *
     * @bodyParam name string required The blueprint name. Example: Product Launch
     * @bodyParam tone_description string optional Tone description for generated posts. Example: Professional and enthusiastic
     * @bodyParam max_characters int optional Max characters per post (default: 280). Example: 200
     * @bodyParam max_hashtags int optional Max hashtags per post (default: 1). Example: 3
     * @bodyParam extra_rules array optional Array of extra rules. Example: ["Always include a call-to-action","Use emojis sparingly"]
     *
     * @response 201 {
     *     "message": "Blueprint created successfully",
     *     "data": {
     *         "id": 1,
     *         "name": "Product Launch",
     *         "tone_description": "Professional and enthusiastic",
     *         "max_characters": 280,
     *         "max_hashtags": 3,
     *         "extra_rules": ["Always include a call-to-action"],
     *         "posts_count": 0,
     *         "created_at": "08/07/2026 10:00",
     *         "updated_at": "08/07/2026 10:00"
     *     }
     * }
     * @responseField data Object The created campaign blueprint.
     *
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "name": ["The blueprint name is required."]
     *     }
     * }
     *
     * Store a newly created blueprint.
     */
    public function store(StoreBlueprintRequest $request): JsonResponse
    {
        $blueprint = CampaignBlueprint::create([
            'user_id'          => $request->user()->id,
            'name'             => $request->name,
            'tone_description' => $request->tone_description,
            'max_characters'   => $request->max_characters ?? 280,
            'max_hashtags'     => $request->max_hashtags ?? 1,
            'extra_rules'      => $request->extra_rules ?? [],
        ]);

        return response()->json([
            'message' => 'Blueprint created successfully',
            'data'    => CampaignBlueprintResource::make($blueprint),
        ], 201);
    }

    /**
     * @group Campaign Blueprints
     *
     * Get a single campaign blueprint by ID.
     *
     * @urlParam blueprint int required The campaign blueprint ID. Example: 1
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Product Launch",
     *         "tone_description": "Professional and enthusiastic",
     *         "max_characters": 280,
     *         "max_hashtags": 3,
     *         "extra_rules": ["Always include a call-to-action"],
     *         "posts_count": 5,
     *         "created_at": "08/07/2026 10:00",
     *         "updated_at": "08/07/2026 12:00"
     *     }
     * }
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     *
     * Display the specified blueprint.
     */
    public function show(Request $request, CampaignBlueprint $campaignBlueprint): JsonResponse
    {
        $this->authorize('view', $campaignBlueprint);

        $campaignBlueprint->load(['rawContents.generatedPost']);

        return response()->json([
            'data' => CampaignBlueprintResource::make($campaignBlueprint),
        ], 200);
    }

    /**
     * @group Campaign Blueprints
     *
     * Update an existing campaign blueprint.
     *
     * @urlParam blueprint int required The campaign blueprint ID. Example: 1
     *
     * @bodyParam name string optional The blueprint name. Example: Product Launch v2
     * @bodyParam tone_description string optional Tone description for generated posts. Example: Casual and witty
     * @bodyParam max_characters int optional Max characters per post. Example: 240
     * @bodyParam max_hashtags int optional Max hashtags per post. Example: 5
     * @bodyParam extra_rules array optional Array of extra rules. Example: ["Include emojis"]
     *
     * @response 200 {
     *     "message": "Blueprint updated successfully",
     *     "data": {
     *         "id": 1,
     *         "name": "Product Launch v2",
     *         "tone_description": "Casual and witty",
     *         "max_characters": 240,
     *         "max_hashtags": 5,
     *         "extra_rules": ["Include emojis"],
     *         "posts_count": 5,
     *         "created_at": "08/07/2026 10:00",
     *         "updated_at": "08/07/2026 14:00"
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
     *         "name": ["The blueprint name cannot exceed 255 characters."]
     *     }
     * }
     *
     * Update the specified blueprint.
     */
    public function update(UpdateBlueprintRequest $request, CampaignBlueprint $campaignBlueprint): JsonResponse
    {
        $this->authorize('update', $campaignBlueprint);

        $campaignBlueprint->update($request->validated());

        return response()->json([
            'message' => 'Blueprint updated successfully',
            'data'    => CampaignBlueprintResource::make($campaignBlueprint),
        ], 200);
    }

    /**
     * @group Campaign Blueprints
     *
     * Delete a campaign blueprint.
     *
     * @urlParam blueprint int required The campaign blueprint ID. Example: 1
     *
     * @response 200 {
     *     "message": "Blueprint deleted successfully"
     * }
     *
     * @response 403 {
     *     "message": "This action is unauthorized."
     * }
     *
     * Remove the specified blueprint.
     */
    public function destroy(Request $request, CampaignBlueprint $campaignBlueprint): JsonResponse
    {
        $this->authorize('delete', $campaignBlueprint);

        $campaignBlueprint->delete();

        return response()->json([
            'message' => 'Blueprint deleted successfully',
        ], 200);
    }
}