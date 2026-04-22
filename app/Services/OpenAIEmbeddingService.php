<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;
use Throwable;

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

        try {
            $payload = [
                'model' => $model,
                'input' => $truncated,
            ];

            if ($dimensions > 0) {
                $payload['dimensions'] = $dimensions;
            }

            $response = OpenAI::embeddings()->create($payload);
        } catch (Throwable $e) {
            Log::error('openai.embeddings_failed', ['message' => $e->getMessage()]);
            throw $e;
        }

        $embedding = $response->embeddings[0]->embedding ?? null;

        if (! is_array($embedding)) {
            throw new RuntimeException('OpenAI embeddings response missing data.');
        }

        /** @var list<float> $embedding */
        return $embedding;
    }
}
