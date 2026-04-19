<?php

use App\Models\CandidateDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fakeVectorSearchStack(): void
{
    Config::set('openai.api_key', 'test-openai-key');
    Config::set('openai.embedding_model', 'text-embedding-3-small');
    Config::set('pinecone.api_key', 'test-pinecone-key');
    Config::set('pinecone.index_host', 'test-index-host.pinecone.io');

    Http::fake(function ($request) {
        $url = (string) $request->url();

        if (str_contains($url, 'api.openai.com/v1/embeddings')) {
            return Http::response([
                'data' => [
                    [
                        'embedding' => array_fill(0, 1536, 0.01),
                    ],
                ],
            ], 200);
        }

        if (str_contains($url, '/query')) {
            $payload = $request->data();
            $filter = is_array($payload) ? ($payload['filter'] ?? null) : null;

            $orgId = null;
            if (is_array($filter) && isset($filter['organization_id']['$eq'])) {
                $orgId = (int) $filter['organization_id']['$eq'];
            }

            $matches = [];
            if ($orgId !== null) {
                $documents = CandidateDocument::query()
                    ->whereHas('candidate', fn ($q) => $q->where('organization_id', $orgId))
                    ->orderBy('id')
                    ->get(['id']);

                foreach ($documents as $document) {
                    $matches[] = [
                        'id' => 'org-'.$orgId.'-doc-'.$document->id,
                        'score' => 0.9,
                        'metadata' => [
                            'organization_id' => $orgId,
                            'candidate_document_id' => $document->id,
                        ],
                    ];
                }
            }

            return Http::response([
                'matches' => $matches,
            ], 200);
        }

        return Http::response([], 404);
    });
}
