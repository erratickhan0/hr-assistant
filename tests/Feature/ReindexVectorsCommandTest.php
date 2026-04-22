<?php

use App\Enums\CandidateDocumentProcessingStatus;
use App\Jobs\ProcessCvUploadJob;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use Illuminate\Support\Facades\Bus;

test('reindex command dispatches jobs for documents missing vector ids', function () {
    Bus::fake();

    $organization = Organization::factory()->create();
    $candidate = Candidate::factory()->for($organization)->create();

    $readyDocument = CandidateDocument::factory()->for($candidate)->create([
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
        'pinecone_vector_id' => null,
    ]);

    CandidateDocument::factory()->for($candidate)->create([
        'processing_status' => CandidateDocumentProcessingStatus::Processing,
        'pinecone_vector_id' => null,
    ]);

    CandidateDocument::factory()->for($candidate)->create([
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
        'pinecone_vector_id' => 'org-1-doc-999',
    ]);

    $this->artisan('cv:reindex-vectors')
        ->assertSuccessful()
        ->expectsOutput('Dispatched 1 CV(s) for embedding/vector reindex.');

    Bus::assertDispatched(ProcessCvUploadJob::class, function (ProcessCvUploadJob $job) use ($readyDocument): bool {
        return $job->document->is($readyDocument);
    });
    Bus::assertDispatched(ProcessCvUploadJob::class, 1);
});

test('reindex command can be scoped to one organization', function () {
    Bus::fake();

    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();

    $candidateA = Candidate::factory()->for($organizationA)->create();
    $candidateB = Candidate::factory()->for($organizationB)->create();

    $docA = CandidateDocument::factory()->for($candidateA)->create([
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
        'pinecone_vector_id' => null,
    ]);

    CandidateDocument::factory()->for($candidateB)->create([
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
        'pinecone_vector_id' => null,
    ]);

    $this->artisan('cv:reindex-vectors', ['--organization' => (string) $organizationA->id])
        ->assertSuccessful();

    Bus::assertDispatched(ProcessCvUploadJob::class, function (ProcessCvUploadJob $job) use ($docA): bool {
        return $job->document->is($docA);
    });
    Bus::assertDispatched(ProcessCvUploadJob::class, 1);
});
