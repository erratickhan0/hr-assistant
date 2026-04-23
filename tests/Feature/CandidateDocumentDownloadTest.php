<?php

use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('organization user can download own candidate document', function () {
    Storage::fake('cv_uploads');

    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();
    $candidate = Candidate::factory()->for($organization)->create();

    $document = CandidateDocument::factory()->for($candidate)->create([
        'disk' => 'cv_uploads',
        'path' => 'cvs/organization-'.$organization->id.'/resume.pdf',
        'original_name' => 'resume.pdf',
    ]);

    Storage::disk('cv_uploads')->put($document->path, 'resume-content');

    $this->actingAs($user)
        ->get(route('documents.download', $document))
        ->assertSuccessful()
        ->assertHeader('content-disposition', 'attachment; filename=resume.pdf');
});

test('organization user can view own candidate document inline', function () {
    Storage::fake('cv_uploads');

    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();
    $candidate = Candidate::factory()->for($organization)->create();

    $document = CandidateDocument::factory()->for($candidate)->create([
        'disk' => 'cv_uploads',
        'path' => 'cvs/organization-'.$organization->id.'/resume.pdf',
        'original_name' => 'resume.pdf',
        'mime' => 'application/pdf',
    ]);

    Storage::disk('cv_uploads')->put($document->path, 'resume-content');

    $this->actingAs($user)
        ->get(route('documents.view', $document))
        ->assertSuccessful()
        ->assertHeader('content-disposition', 'inline; filename=resume.pdf');
});

test('organization user cannot download another organization document', function () {
    Storage::fake('cv_uploads');

    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();
    $userA = User::factory()->for($organizationA)->create();
    $candidateB = Candidate::factory()->for($organizationB)->create();

    $document = CandidateDocument::factory()->for($candidateB)->create([
        'disk' => 'cv_uploads',
        'path' => 'cvs/organization-'.$organizationB->id.'/resume.pdf',
        'original_name' => 'resume.pdf',
    ]);

    Storage::disk('cv_uploads')->put($document->path, 'resume-content');

    $this->actingAs($userA)
        ->get(route('documents.download', $document))
        ->assertForbidden();
});

test('organization user cannot view another organization document', function () {
    Storage::fake('cv_uploads');

    $organizationA = Organization::factory()->create();
    $organizationB = Organization::factory()->create();
    $userA = User::factory()->for($organizationA)->create();
    $candidateB = Candidate::factory()->for($organizationB)->create();

    $document = CandidateDocument::factory()->for($candidateB)->create([
        'disk' => 'cv_uploads',
        'path' => 'cvs/organization-'.$organizationB->id.'/resume.pdf',
        'original_name' => 'resume.pdf',
    ]);

    Storage::disk('cv_uploads')->put($document->path, 'resume-content');

    $this->actingAs($userA)
        ->get(route('documents.view', $document))
        ->assertForbidden();
});
