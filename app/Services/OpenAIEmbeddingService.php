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
        $truncated = Str::limit($input, 28_000, '');

        $response = Http::withToken((string) config('openai.api_key'))
            ->timeout(120)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $model,
                'input' => $truncated,
            ]);

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
