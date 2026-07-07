<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRawContentRequest;
use App\Http\Resources\RawContentResource;
use App\Jobs\ProcessRawContentJob;
use App\Models\RawContent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RawContentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the authenticated user's raw contents.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $rawContents = RawContent::query()
            ->where('user_id', $request->user()->id)
            ->with(['generatedPost', 'campaignBlueprint'])
            ->latest()
            ->get();

        return RawContentResource::collection($rawContents);
    }

    /**
     * Store a newly created raw content and dispatch the processing job.
     */
    public function store(StoreRawContentRequest $request): JsonResponse
    {
        // Vérifier que le blueprint appartient à l'utilisateur connecté
        $blueprint = $request->user()
            ->campaignBlueprints()
            ->findOrFail($request->blueprint_id);

        // Créer le RawContent
        $rawContent = RawContent::create([
            'user_id'      => $request->user()->id,
            'blueprint_id' => $blueprint->id,
            'content'      => $request->content,
            'status'       => 'draft',
        ]);

        // Dispatcher le Job asynchrone → réponse immédiate 202
        ProcessRawContentJob::dispatch($rawContent);

        return response()->json([
            'message' => 'Content submitted successfully. Processing in background.',
            'data'    => RawContentResource::make($rawContent),
        ], 202);
    }

    /**
     * Display the specified raw content.
     */
    public function show(Request $request, RawContent $rawContent): JsonResponse
    {
        // Vérifier que le rawContent appartient à l'utilisateur connecté
        if ($rawContent->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        $rawContent->load(['generatedPost', 'campaignBlueprint']);

        return response()->json([
            'data' => RawContentResource::make($rawContent),
        ], 200);
    }
}