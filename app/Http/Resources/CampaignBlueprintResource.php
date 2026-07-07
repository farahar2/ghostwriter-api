<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignBlueprintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'tone_description' => $this->tone_description,
            'max_characters'   => $this->max_characters,
            'max_hashtags'     => $this->max_hashtags,
            'extra_rules'      => $this->extra_rules ?? [],
            'posts_count'      => $this->whenLoaded('rawContents', function () {
                return $this->rawContents->sum(function ($rawContent) {
                    return $rawContent->generatedPost ? 1 : 0;
                });
            }, 0),
            'created_at'       => $this->created_at->format('d/m/Y H:i'),
            'updated_at'       => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}