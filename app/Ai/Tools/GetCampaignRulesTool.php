<?php

namespace App\Ai\Tools;

use App\Models\CampaignBlueprint;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCampaignRulesTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get the rules of a campaign blueprint by its ID. Returns name, tone_description, max_characters, max_hashtags, and extra_rules.';
    }

    public function handle(Request $request): Stringable|string
    {
        $blueprint = CampaignBlueprint::find($request['campaign_id']);

        if (! $blueprint) {
            return json_encode(['error' => 'Campaign blueprint not found.']);
        }

        return json_encode([
            'name'             => $blueprint->name,
            'tone_description' => $blueprint->tone_description,
            'max_characters'   => $blueprint->max_characters,
            'max_hashtags'     => $blueprint->max_hashtags,
            'extra_rules'      => $blueprint->extra_rules ?? [],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'campaign_id' => $schema->integer()
                ->description('The ID of the campaign blueprint to retrieve rules for.')
                ->required(),
        ];
    }
}
