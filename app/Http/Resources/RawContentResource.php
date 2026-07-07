<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'blueprint_id' => $this->blueprint_id,
            'content'      => $this->content,
            'status'       => $this->status,
            'generated_post' => GeneratedPostResource::make($this->whenLoaded('generatedPost')),
            'created_at'   => $this->created_at->format('d/m/Y H:i'),
            'updated_at'   => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}