<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'generated_post_id' => $this->generated_post_id,
            'messages'         => ChatMessageResource::collection($this->whenLoaded('chatMessages')),
            'created_at'       => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
