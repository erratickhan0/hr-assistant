<?php

namespace App\Services;

use App\Models\CandidateDocument;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
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
                $filter = [
                    'organization_id' => ['$eq' => $organization->id],
                ];
                $matches = $this->pinecone->query($vector, $limit, null, $filter);

                $idScores = [];
                foreach ($matches as $match) {
                    $meta = $match['metadata'] ?? [];
                    if (isset($meta['candidate_document_id']) && is_numeric($meta['candidate_document_id'])) {
                        $id = (int) $meta['candidate_document_id'];
                        $idScores[$id] = $match['score'];
                    }
                }

                if ($idScores === []) {
                    return [];
                }

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
            } catch (Throwable $e) {
                Log::warning('hr_search.vector_failed', ['message' => $e->getMessage()]);
            }
        }

        return $this->keywordFallbackWithScores($organization, $query, $limit);
    }

    /**
     * @return list<array{document: CandidateDocument, score: float|null}>
     */
    private function keywordFallbackWithScores(Organization $organization, string $query, int $limit): array
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
        $needle = '%'.$escaped.'%';

        $documents = CandidateDocument::query()
            ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
            ->where('original_name', 'like', $needle)
            ->with('candidate')
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($documents as $document) {
            $out[] = ['document' => $document, 'score' => null];
        }

        return $out;
    }
}
