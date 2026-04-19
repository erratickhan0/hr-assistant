<?php

namespace App\Services;

use App\Models\CandidateDocument;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class HrCandidateSearchService
{
    public function __construct(
        private readonly OpenAIEmbeddingService $openai,
        private readonly PineconeVectorService $pinecone,
    ) {}

    /**
     * @return Collection<int, CandidateDocument>
     */
    public function search(Organization $organization, string $query, int $limit = 15): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return new Collection;
        }

        if ($this->openai->isConfigured() && $this->pinecone->isConfigured()) {
            try {
                $vector = $this->openai->embed($query);
                $filter = [
                    'organization_id' => ['$eq' => $organization->id],
                ];
                $matches = $this->pinecone->query($vector, $limit, null, $filter);

                $ids = [];
                foreach ($matches as $match) {
                    $meta = $match['metadata'] ?? [];
                    if (isset($meta['candidate_document_id']) && is_numeric($meta['candidate_document_id'])) {
                        $ids[] = (int) $meta['candidate_document_id'];
                    }
                }

                if ($ids === []) {
                    return new Collection;
                }

                return CandidateDocument::query()
                    ->whereIn('id', $ids)
                    ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
                    ->with('candidate')
                    ->get()
                    ->sortBy(fn (CandidateDocument $doc) => array_search($doc->id, $ids, true))
                    ->values();
            } catch (Throwable $e) {
                Log::warning('hr_search.vector_failed', ['message' => $e->getMessage()]);
            }
        }

        return $this->keywordFallback($organization, $query, $limit);
    }

    /**
     * @return Collection<int, CandidateDocument>
     */
    private function keywordFallback(Organization $organization, string $query, int $limit): Collection
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
        $needle = '%'.$escaped.'%';

        return CandidateDocument::query()
            ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
            ->whereNotNull('extracted_text')
            ->where(function ($q) use ($needle): void {
                $q->where('extracted_text', 'like', $needle)
                    ->orWhere('original_name', 'like', $needle);
            })
            ->with('candidate')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }
}
