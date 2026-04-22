<?php

use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use App\Models\User;

test('candidates page lists organization candidates with pagination', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();

    $candidate = Candidate::factory()->for($organization)->create([
        'display_name' => 'Alex Resume',
    ]);
    CandidateDocument::factory()->for($candidate)->create([
        'original_name' => 'alex_resume.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('candidates.index'))
        ->assertOk()
        ->assertSee('Candidates', false)
        ->assertSee('Alex Resume', false)
        ->assertSee('alex_resume.pdf', false);
});

test('candidates page can search by resume filename', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();

    $candidateA = Candidate::factory()->for($organization)->create([
        'display_name' => 'Carpenter',
    ]);
    CandidateDocument::factory()->for($candidateA)->create([
        'original_name' => 'carpenter_resume.docx',
    ]);

    $candidateB = Candidate::factory()->for($organization)->create([
        'display_name' => 'Electrician',
    ]);
    CandidateDocument::factory()->for($candidateB)->create([
        'original_name' => 'electrician_resume.docx',
    ]);

    $this->actingAs($user)
        ->get(route('candidates.index', ['q' => 'carpenter']))
        ->assertOk()
        ->assertSee('carpenter_resume.docx', false)
        ->assertDontSee('electrician_resume.docx', false);
});

test('candidates page can search by candidate name and email', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->for($organization)->create();

    $candidateA = Candidate::factory()->for($organization)->create([
        'display_name' => 'Jamie Wood',
        'email' => 'jamie.wood@example.test',
    ]);
    CandidateDocument::factory()->for($candidateA)->create([
        'original_name' => 'wood_resume.docx',
    ]);

    $candidateB = Candidate::factory()->for($organization)->create([
        'display_name' => 'Alex Stone',
        'email' => 'alex.stone@example.test',
    ]);
    CandidateDocument::factory()->for($candidateB)->create([
        'original_name' => 'stone_resume.docx',
    ]);

    $this->actingAs($user)
        ->get(route('candidates.index', ['q' => 'jamie wood']))
        ->assertOk()
        ->assertSee('Jamie Wood', false)
        ->assertDontSee('Alex Stone', false);

    $this->actingAs($user)
        ->get(route('candidates.index', ['q' => 'alex.stone@example.test']))
        ->assertOk()
        ->assertSee('Alex Stone', false)
        ->assertDontSee('Jamie Wood', false);
});
