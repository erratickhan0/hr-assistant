<?php

namespace Database\Factories;

use App\Enums\CandidateDocumentProcessingStatus;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateDocument>
 */
class CandidateDocumentFactory extends Factory
{
    protected $model = CandidateDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'original_name' => 'cv.pdf',
            'disk' => 'local',
            'path' => 'cvs/test.pdf',
            'mime' => 'application/pdf',
            'size_bytes' => 1024,
            'extracted_text' => null,
            'processing_status' => CandidateDocumentProcessingStatus::Ready,
            'pinecone_vector_id' => null,
            'embedding_model' => null,
            'embedding_dimensions' => null,
            'indexed_at' => null,
            'last_error' => null,
        ];
    }
}
