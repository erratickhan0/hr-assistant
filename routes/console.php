<?php

use App\Enums\CandidateDocumentProcessingStatus;
use App\Jobs\ProcessCvUploadJob;
use App\Models\CandidateDocument;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cv:reindex-vectors {--organization= : Only reindex one organization id} {--limit=100 : Max documents to dispatch}', function (): int {
    $organizationId = $this->option('organization');
    $limit = max(1, (int) $this->option('limit'));

    $query = CandidateDocument::query()
        ->whereNull('pinecone_vector_id')
        ->whereIn('processing_status', [
            CandidateDocumentProcessingStatus::Ready,
            CandidateDocumentProcessingStatus::Failed,
        ])
        ->whereHas('candidate', function ($candidateQuery) use ($organizationId): void {
            if ($organizationId !== null && $organizationId !== '') {
                $candidateQuery->where('organization_id', (int) $organizationId);
            }
        })
        ->orderBy('id')
        ->limit($limit);

    $documents = $query->get();

    if ($documents->isEmpty()) {
        $this->info('No candidate documents need vector reindexing.');

        return self::SUCCESS;
    }

    foreach ($documents as $document) {
        ProcessCvUploadJob::dispatch($document);
    }

    $this->info(sprintf('Dispatched %d CV(s) for embedding/vector reindex.', $documents->count()));

    return self::SUCCESS;
})->purpose('Queue CV documents with missing Pinecone vectors for reindexing');
