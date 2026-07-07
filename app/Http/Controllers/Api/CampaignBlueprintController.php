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