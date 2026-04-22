<?php

namespace App\Services;

use App\Models\CandidateDocument;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HrCandidateSearchService
{
    public function __construct(
        private readonly OpenAIEmbeddingService $openai,
        private readonly PineconeVectorService $pinecone,
    ) {}

    /**
     * @return EloquentCollection<int, CandidateDocument>
     */
    public function search(Organization $organization, string $query, int $limit = 15): EloquentCollection
    {
        $documents = collect($this->searchWithScores($organization, $query, $limit))
            ->pluck('document')
            ->all();

        return new EloquentCollection($documents);
    }

    /**
     * @return list<array{document: CandidateDocument, score: float|null}>
     */
    public function searchWithScores(Organization $organization, string $query, int $limit = 15): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        if ($this->openai->isConfigured() && $this->pinecone->isConfigured()) {
            try {
                $vector = $this->openai->embed($query);
                $matches = $this->pinecone->query($vector, $limit, null, [
                    'organization_id' => ['$eq' => $organization->id],
                ]);

                $idScores = [];
                foreach ($matches as $match) {
                    $meta = $match['metadata'] ?? [];
                    if (isset($meta['candidate_document_id']) && is_numeric($meta['candidate_document_id'])) {
                        $idScores[(int) $meta['candidate_document_id']] = (float) ($match['score'] ?? 0.0);
                    }
                }

                if ($idScores !== []) {
                    $ids = array_keys($idScores);
                    $documents = CandidateDocument::query()
                        ->whereIn('id', $ids)
                        ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
                        ->with('candidate')
                        ->get()
                        ->keyBy('id');

                    $ordered = [];
                    foreach ($ids as $id) {
                        $doc = $documents->get($id);
                        if ($doc !== null) {
                            $ordered[] = [
                                'document' => $doc,
                                'score' => $idScores[$id] ?? null,
                            ];
                        }
                    }

                    return $ordered;
                }
            } catch (Throwable $e) {
                Log::warning('hr_search.vector_failed', ['message' => $e->getMessage()]);
            }
        }

        return $this->resumeNameSearchWithScores($organization, $query, $limit);
    }

    /**
     * @return list<array{document: CandidateDocument, score: float|null}>
     */
    private function resumeNameSearchWithScores(Organization $organization, string $query, int $limit): array
    {
        $terms = $this->tokenizeQuery($query);
        if ($terms === []) {
            return [];
        }

        $phrase = mb_strtolower($query);

        $documents = CandidateDocument::query()
            ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
            ->where(function ($q) use ($terms): void {
                foreach ($terms as $term) {
                    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
                    $q->whereRaw('LOWER(original_name) like ?', ['%'.$escaped.'%']);
                }
            })
            ->with('candidate')
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($documents as $document) {
            $name = mb_strtolower($document->original_name);
            $termHits = 0;
            foreach ($terms as $term) {
                if (str_contains($name, $term)) {
                    $termHits++;
                }
            }

            $score = (float) $termHits;
            if (str_contains($name, $phrase)) {
                $score += 2.0;
            }
            if (Str::startsWith($name, $terms[0])) {
                $score += 0.5;
            }

            $out[] = ['document' => $document, 'score' => $score];
        }

        usort($out, fn (array $a, array $b): int => ($b['score'] <=> $a['score']));

        return array_values($out);
    }

    /**
     * @return list<string>
     */
    private function tokenizeQuery(string $query): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', mb_strtolower(trim($query))) ?: [];
        $tokens = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) >= 2) {
                $tokens[] = $part;
            }
        }

        return array_values(array_unique($tokens));
    }
}
