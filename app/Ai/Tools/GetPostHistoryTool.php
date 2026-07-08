<?php

namespace App\Ai\Tools;

use App\Models\GeneratedPost;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetPostHistoryTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get the full history and details of a generated post by its ID. Returns hook, body points, readability score, hashtags, tone justification, and status.';
    }

    public function handle(Request $request): Stringable|string
    {
        $post = GeneratedPost::with(['rawContent.campaignBlueprint'])->find($request['post_id']);

        if (! $post) {
            return json_encode(['error' => 'Generated post not found.']);
        }

        return json_encode([
            'hook_propose'                  => $post->hook_propose,
            'body_points'                   => $post->body_points ?? [],
            'technical_readability_score'   => $post->technical_readability_score,
            'suggested_hashtags'            => $post->suggested_hashtags ?? [],
            'tone_compliance_justification' => $post->tone_compliance_justification,
            'status'                        => $post->status,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->integer()
                ->description('The ID of the generated post to retrieve history for.')
                ->required(),
        ];
    }
}
