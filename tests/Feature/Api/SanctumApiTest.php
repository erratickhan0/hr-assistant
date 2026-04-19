<?php

use App\Enums\CandidateDocumentProcessingStatus;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

test('guest receives 422 when api token credentials are invalid', function () {
    $organization = Organization::factory()->create(['slug' => 'api-cred-test']);

    $this->postJson('/api/auth/token', [
        'organization_slug' => 'api-cred-test',
        'email' => 'nobody@example.test',
        'password' => 'wrong-password',
    ])->assertStatus(422)
        ->assertJsonFragment(['message' => __('These credentials do not match our records.')]);
});

test('hr user can issue a personal access token and call protected api routes', function () {
    fakeVectorSearchStack();

    $organization = Organization::factory()->create(['slug' => 'api-token-test']);
    $user = User::factory()->for($organization)->create([
        'email' => 'api@example.test',
    ]);

    $tokenResponse = $this->postJson('/api/auth/token', [
        'organization_slug' => 'api-token-test',
        'email' => 'api@example.test',
        'password' => 'password',
        'device_name' => 'pest',
    ])->assertOk()
        ->assertJsonStructure([
            'token',
            'token_type',
            'user' => ['id', 'name', 'email', 'role', 'organization'],
            'organization' => ['id', 'uuid', 'name', 'slug'],
        ]);

    $plainToken = $tokenResponse->json('token');
    expect($plainToken)->toBeString()->not->toBeEmpty();

    $this->getJson('/api/user', [
        'Authorization' => 'Bearer '.$plainToken,
    ])->assertOk()
        ->assertJsonPath('data.email', 'api@example.test')
        ->assertJsonPath('data.organization.slug', 'api-token-test');

    $candidate = Candidate::factory()->for($organization)->create();
    $extractedPath = 'extracted/organization-'.$organization->id.'/doc-test.txt';
    Storage::disk('local')->put($extractedPath, 'Rust systems programming background');

    CandidateDocument::factory()->for($candidate)->create([
        'extracted_text_path' => $extractedPath,
        'processing_status' => CandidateDocumentProcessingStatus::Ready,
    ]);

    $this->getJson('/api/candidates', [
        'Authorization' => 'Bearer '.$plainToken,
    ])->assertOk()
        ->assertJsonPath('data.0.email', $candidate->email);

    $search = $this->postJson('/api/search', ['q' => 'Rust'], [
        'Authorization' => 'Bearer '.$plainToken,
    ])->assertOk()
        ->assertJsonPath('data.query', 'Rust');

    expect($search->json('data.matches.0.document.excerpt'))->toContain('Rust');

    $this->deleteJson('/api/auth/token', [], [
        'Authorization' => 'Bearer '.$plainToken,
    ])->assertStatus(204);

    expect(PersonalAccessToken::query()->count())->toBe(0);

    // Sanctum's RequestGuard caches the user for the PHP process; clear it so the
    // next request re-resolves from the (now revoked) bearer token like production.
    $this->app['auth']->guard('sanctum')->forgetUser();

    $this->getJson('/api/user', [
        'Authorization' => 'Bearer '.$plainToken,
    ])->assertStatus(401);
});

test('guest cannot access protected api routes without a token', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});
