<?php

namespace App\Http\Resources\Api;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Candidate
 */
class CandidateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'created_at' => $this->created_at?->toIso8601String(),
            'documents' => CandidateDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
