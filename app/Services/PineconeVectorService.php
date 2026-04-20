<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin HTTP wrapper for Pinecone upsert/query. Configure PINECONE_* in .env.
 *
 * @see https://docs.pinecone.io/reference/api/data-plane/upsert
 */
class PineconeVectorService
{
    public function isConfigured(): bool
    {
        return filled(config('pinecone.api_key')) && filled(config('pinecone.index_host'));
    }

    /**
     * @param  list<float>  $values
     * @param  array<string, mixed>  $metadata  Must stay small; Pinecone limits apply.
     */
    public function upsert(string $id, array $values, array $metadata = []): void
    {
        $this->requireConfigured();

        $url = $this->dataPlaneUrl('/vectors/upsert');

        $response = Http::withHeaders([
            'Api-Key' => (string) config('pinecone.api_key'),
            'Content-Type' => 'application/json',
        ])->post($url, [
            'vectors' => [
                [
                    'id' => $id,
                    'values' => $values,
                    'metadata' => $metadata,
                ],
            ],
        ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            Log::error('pinecone.upsert_failed', ['id' => $id, 'body' => $response->body()]);
            throw $e;
        }
    }

    /**
     * @param  list<float>  $queryVector
     * @param  array<string, mixed>|null  $filter  Pinecone metadata filter, e.g. ['organization_id' => ['$eq' => 1]]
     * @return list<array{id: string, score: float, metadata?: array<string, mixed>}>
     */
    public function query(array $queryVector, int $topK = 10, ?string $namespace = null, ?array $filter = null): array
    {
        $this->requireConfigured();

        $payload = [
            'vector' => $queryVector,
            'topK' => $topK,
            'includeMetadata' => true,
        ];

        if ($filter !== null && $filter !== []) {
            $payload['filter'] = $filter;
        }

        if ($namespace !== null && $namespace !== '') {
            $payload['namespace'] = $namespace;
        }

        $url = $this->dataPlaneUrl('/query');

        $response = Http::withHeaders([
            'Api-Key' => (string) config('pinecone.api_key'),
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        $response->throw();

        $matches = $response->json('matches') ?? [];

        $out = [];
        foreach ($matches as $match) {
            $out[] = [
                'id' => (string) ($match['id'] ?? ''),
                'score' => (float) ($match['score'] ?? 0.0),
                'metadata' => is_array($match['metadata'] ?? null) ? $match['metadata'] : [],
            ];
        }

        return $out;
    }

    public function deleteByIds(array $ids, ?string $namespace = null): void
    {
        if ($ids === []) {
            return;
        }

        $this->requireConfigured();

        $payload = ['ids' => array_values($ids)];
        if ($namespace !== null && $namespace !== '') {
            $payload['namespace'] = $namespace;
        }

        $url = $this->dataPlaneUrl('/vectors/delete');

        Http::withHeaders([
            'Api-Key' => (string) config('pinecone.api_key'),
            'Content-Type' => 'application/json',
        ])->post($url, $payload)->throw();
    }

    private function dataPlaneUrl(string $path): string
    {
        $host = trim((string) config('pinecone.index_host'));
        $host = preg_replace('#^https?://#i', '', $host) ?? $host;
        $host = rtrim($host, '/');

        return 'https://'.$host.$path;
    }

    private function requireConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Pinecone is not configured. Set PINECONE_API_KEY and PINECONE_INDEX_HOST in .env.');
        }
    }
}
