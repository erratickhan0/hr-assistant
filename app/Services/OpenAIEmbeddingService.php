<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAIEmbeddingService
{
    public function isConfigured(): bool
    {
        return filled(config('openai.api_key'));
    }

    /**
     * @return list<float>
     */
    public function embed(string $input): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI is not configured. Set OPENAI_API_KEY in .env.');
        }

        $model = (string) config('openai.embedding_model');
        $dimensions = (int) config('openai.embedding_dimensions', 512);
        $truncated = Str::limit($input, 28_000, '');

        $payload = [
            'model' => $model,
            'input' => $truncated,
        ];

        if ($dimensions > 0) {
            $payload['dimensions'] = $dimensions;
        }

        $response = Http::withToken((string) config('openai.api_key'))
            ->timeout(120)
            ->post('https://api.openai.com/v1/embeddings', $payload);

        try {
            $response->throw();
        } catch (RequestException $e) {
            Log::error('openai.embeddings_failed', ['body' => $response->body()]);
            throw $e;
        }

        $embedding = $response->json('data.0.embedding');

        if (! is_array($embedding)) {
            throw new RuntimeException('OpenAI embeddings response missing data.');
        }

        /** @var list<float> $embedding */
        return $embedding;
    }
}
