<?php

namespace App\Http\Resources\Api;

use App\Models\CandidateDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * @mixin CandidateDocument
 */
class CandidateDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime' => $this->mime,
            'size_bytes' => $this->size_bytes,
            'processing_status' => $this->processing_status->value,
            'pinecone_vector_id' => $this->pinecone_vector_id,
            'indexed_at' => $this->indexed_at?->toIso8601String(),
            'last_error' => $this->last_error,
            'excerpt' => $this->excerptFromObjectStorage(),
            'candidate' => $this->when(
                $this->relationLoaded('candidate') && $this->candidate !== null,
                fn (): array => [
                    'id' => $this->candidate->id,
                    'uuid' => $this->candidate->uuid,
                    'display_name' => $this->candidate->display_name,
                    'email' => $this->candidate->email,
                ],
            ),
        ];
    }

    private function excerptFromObjectStorage(): ?string
    {
        if ($this->extracted_text_path === null || $this->extracted_text_path === '') {
            return null;
        }

        try {
            $disk = Storage::disk($this->disk);
            if (! $disk->exists($this->extracted_text_path)) {
                return null;
            }

            $raw = $disk->get($this->extracted_text_path);
            if (! is_string($raw) || $raw === '') {
                return null;
            }

            return Str::limit(strip_tags($raw), 400);
        } catch (Throwable) {
            return null;
        }
    }
}
