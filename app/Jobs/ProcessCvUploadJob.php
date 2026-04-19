<?php

namespace App\Jobs;

use App\Enums\CandidateDocumentProcessingStatus;
use App\Models\CandidateDocument;
use App\Services\CvTextExtractionService;
use App\Services\OpenAIEmbeddingService;
use App\Services\PineconeVectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessCvUploadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CandidateDocument $document,
    ) {}

    public function handle(
        CvTextExtractionService $textExtraction,
        OpenAIEmbeddingService $openai,
        PineconeVectorService $pinecone,
    ): void {
        $document = $this->document->fresh(['candidate']);
        if ($document === null || $document->candidate === null) {
            return;
        }

        $document->forceFill([
            'processing_status' => CandidateDocumentProcessingStatus::Processing,
            'last_error' => null,
        ])->save();

        try {
            $disk = Storage::disk($document->disk);
            $cvTempPath = tempnam(sys_get_temp_dir(), 'cv_upload_');
            if ($cvTempPath === false) {
                throw new \RuntimeException('Could not create a temporary file for CV processing.');
            }

            try {
                file_put_contents($cvTempPath, $disk->get($document->path));
                $text = $textExtraction->extract($cvTempPath, $document->mime);
            } finally {
                if (is_file($cvTempPath)) {
                    @unlink($cvTempPath);
                }
            }

            if ($text === '') {
                $document->forceFill([
                    'processing_status' => CandidateDocumentProcessingStatus::Failed,
                    'last_error' => 'No text could be extracted from this file.',
                ])->save();

                return;
            }

            $extractedRelativePath = sprintf(
                'extracted/organization-%d/doc-%d-%s.txt',
                (int) $document->candidate->organization_id,
                (int) $document->id,
                (string) Str::uuid(),
            );

            $disk->put($extractedRelativePath, $text, [
                'visibility' => 'private',
                'ContentType' => 'text/plain; charset=UTF-8',
            ]);

            $document->forceFill([
                'extracted_text_path' => $extractedRelativePath,
            ])->save();

            if (! $openai->isConfigured() || ! $pinecone->isConfigured()) {
                $document->forceFill([
                    'processing_status' => CandidateDocumentProcessingStatus::Ready,
                    'pinecone_vector_id' => null,
                    'last_error' => 'OpenAI/Pinecone not configured — extracted text stored on object storage only.',
                ])->save();

                return;
            }

            $vector = $openai->embed($text);
            $orgId = $document->candidate->organization_id;
            $vectorId = 'org-'.$orgId.'-doc-'.$document->id;

            $pinecone->upsert($vectorId, $vector, [
                'organization_id' => $orgId,
                'candidate_id' => $document->candidate_id,
                'candidate_document_id' => $document->id,
            ]);

            $document->forceFill([
                'processing_status' => CandidateDocumentProcessingStatus::Ready,
                'pinecone_vector_id' => $vectorId,
                'embedding_model' => (string) config('openai.embedding_model'),
                'embedding_dimensions' => count($vector),
                'indexed_at' => now(),
                'last_error' => null,
            ])->save();
        } catch (Throwable $e) {
            Log::error('process_cv_upload_failed', [
                'document_id' => $document->id,
                'message' => $e->getMessage(),
            ]);

            $document->forceFill([
                'processing_status' => CandidateDocumentProcessingStatus::Failed,
                'last_error' => $e->getMessage(),
            ])->save();
        }
    }
}
