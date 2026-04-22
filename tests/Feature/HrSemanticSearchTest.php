<?php

use App\Enums\CandidateDocumentProcessingStatus;
use App\Jobs\ProcessCvUploadJob;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('hr can keyword search extracted cv text', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();
    $candidate = Candidate::factory()->for($organization)->create();
    $document = CandidateDocument::factory()->for($candidate)->create([
        'original_name' => 'Senior-Laravel-engineer-resume.pdf',
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
    ]);

    $this->actingAs($user)
        ->post(route('hr.search'), ['q' => 'Laravel'])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('search_query')
        ->assertSessionHas('search_mode', 'semantic_text')
        ->assertSessionHas('search_result_ids.0', $document->id);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('semantic search', false)
        ->assertSee('Senior-Laravel-engineer-resume.pdf', false);
});

test('cv upload dispatches processing job', function () {
    Queue::fake();
    Storage::fake('cv_uploads');

    $organization = Organization::factory()->create(['slug' => 'upload-test']);
    $file = UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf');

    $this->post(route('agency.cv.store', $organization), [
        'display_name' => 'Jamie',
        'email' => 'jamie@example.test',
        'cv' => $file,
    ])->assertRedirect(route('agency.portal', $organization));

    Queue::assertPushed(ProcessCvUploadJob::class);
});
