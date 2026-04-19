<?php

namespace App\Models;

use App\Enums\CandidateDocumentProcessingStatus;
use Database\Factories\CandidateDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property int $candidate_id
 * @property string $original_name
 * @property string $disk
 * @property string $path
 * @property string $mime
 * @property int $size_bytes
 * @property string|null $extracted_text_path
 * @property CandidateDocumentProcessingStatus $processing_status
 * @property string|null $pinecone_vector_id
 * @property string|null $embedding_model
 * @property int|null $embedding_dimensions
 * @property Carbon|null $indexed_at
 * @property string|null $last_error
 */
class CandidateDocument extends Model
{
    /** @use HasFactory<CandidateDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'original_name',
        'disk',
        'path',
        'mime',
        'size_bytes',
        'extracted_text_path',
        'processing_status',
        'pinecone_vector_id',
        'embedding_model',
        'embedding_dimensions',
        'indexed_at',
        'last_error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'processing_status' => CandidateDocumentProcessingStatus::class,
            'indexed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
