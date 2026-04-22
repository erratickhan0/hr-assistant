<?php

namespace App\Services;

use App\Models\CandidateDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class OpenAICvAnswerService
{
    public function isConfigured(): bool
    {
        return filled(config('openai.api_key'));
    }

    /**
     * Generate a short answer grounded in extracted CV text for the top matches.
     *
     * @param  Collection<int, CandidateDocument>|iterable  $documents
     */
    public function answerForDocuments(string $query, iterable $documents, int $maxDocuments = 5): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $docs = Collection::make($documents)->take($maxDocuments);
        if ($docs->isEmpty()) {
            return null;
        }

        $excerpts = [];
        $remaining = 24_000;

        foreach ($docs as $doc) {
            $name = $doc->candidate?->display_name ?? 'Candidate';
            $file = $doc->original_name;
            $text = $this->loadExtractedText($doc);
            if ($text === null || $text === '') {
                $chunk = "[{$name} — {$file}]\n(Extracted text not available yet; run CV processing or re-upload.)";
            } else {
                $take = min($remaining, 8_000);
                $chunk = "[{$name} — {$file}]\n".Str::limit($text, $take, '…');
            }
            $excerpts[] = $chunk;
            $remaining -= strlen($chunk);
            if ($remaining < 500) {
                break;
            }
        }

        $context = implode("\n\n---\n\n", $excerpts);

        $model = (string) config('openai.answer_model', 'gpt-4o-mini');

        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an HR assistant. Answer the recruiter\'s question using ONLY the CV excerpts below. If the excerpts do not mention something, say clearly that it is not stated in the provided text. Name which candidate each point refers to. Be concise. Do not invent skills, employers, or dates not supported by the excerpts.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Question: {$query}\n\nCV excerpts:\n{$context}",
                    ],
                ],
                'max_tokens' => 600,
                'temperature' => 0.2,
            ]);
        } catch (Throwable $e) {
            Log::warning('openai.cv_answer_failed', ['message' => $e->getMessage()]);

            return null;
        }

        $content = $response->choices[0]->message->content ?? null;
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        return trim($content);
    }

    /**
     * Build deterministic keyword evidence from extracted CV text for UI explainability.
     *
     * @param  Collection<int, CandidateDocument>|iterable  $documents
     * @return array<int, array{
     *   matched_terms: list<string>,
     *   match_count: int,
     *   phrase_count: int,
     *   has_all_terms: bool,
     *   snippet: string|null
     * }>
     */
    public function evidenceForDocuments(string $query, iterable $documents): array
    {
        $terms = $this->extractSearchTerms($query);
        if ($terms === []) {
            return [];
        }
        $phrase = mb_strtolower(trim($query));

        $evidence = [];

        foreach (Collection::make($documents) as $doc) {
            if (! $doc instanceof CandidateDocument) {
                continue;
            }

            $text = $this->loadExtractedText($doc);
            if ($text === null || $text === '') {
                continue;
            }

            $lowerText = mb_strtolower($text);
            $matchedTerms = [];
            $matchCount = 0;
            $firstPos = null;
            $phraseCount = 0;

            if ($phrase !== '' && mb_strlen($phrase) >= 3) {
                $phraseCount = substr_count($lowerText, $phrase);
            }

            foreach ($terms as $term) {
                $count = substr_count($lowerText, $term);
                if ($count > 0) {
                    $matchedTerms[] = $term;
                    $matchCount += $count;

                    $pos = mb_stripos($text, $term);
                    if ($pos !== false && ($firstPos === null || $pos < $firstPos)) {
                        $firstPos = $pos;
                    }
                }
            }

            if ($matchedTerms === []) {
                continue;
            }

            $evidence[$doc->id] = [
                'matched_terms' => $matchedTerms,
                'match_count' => $matchCount,
                'phrase_count' => $phraseCount,
                'has_all_terms' => count($matchedTerms) === count($terms),
                'snippet' => $this->makeSnippet($text, $firstPos),
            ];
        }

        return $evidence;
    }

    private function loadExtractedText(CandidateDocument $document): ?string
    {
        $path = $document->extracted_text_path;
        if ($path === null || $path === '') {
            return null;
        }

        try {
            $disk = Storage::disk($document->disk);
            if (! $disk->exists($path)) {
                return null;
            }

            $raw = $disk->get($path);

            return is_string($raw) ? $raw : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function extractSearchTerms(string $query): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', mb_strtolower($query)) ?: [];
        $terms = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) >= 3) {
                $terms[] = $part;
            }
        }

        return array_values(array_unique($terms));
    }

    private function makeSnippet(string $text, ?int $center): ?string
    {
        if ($center === null) {
            return null;
        }

        $start = max(0, $center - 120);
        $snippet = mb_substr($text, $start, 280);

        return trim(preg_replace('/\s+/', ' ', $snippet) ?? '');
    }
}
