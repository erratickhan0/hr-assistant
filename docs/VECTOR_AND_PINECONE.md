# Vectors, Pinecone, and how this MVP fits together

## Are the tools right?

**Yes for a client-demo SaaS MVP** — this stack is a common pattern:

| Piece | Role |
|--------|------|
| **Laravel + MySQL/SQLite** | Source of truth: agencies (`organizations`), HR users, candidates, **object storage paths** (CV + extracted text), **processing status**, **Pinecone vector id**. |
| **OpenAI Embeddings** | Turns CV text and HR questions into **dense vectors** (arrays of floats). |
| **Pinecone** | **Vector index** optimized for similarity search at scale; not a replacement for SQL. |

**Pinecone strengths:** managed hosting, simple HTTP API, good for demos and moderate scale, namespaces for optional tenant separation.

**Alternatives (when to reconsider):**

- **`pgvector` (PostgreSQL)** — fewer moving parts if you already run Postgres; great when you want **one database** for rows + vectors. Slightly more ops if you are on MySQL today.
- **Qdrant / Weaviate / Milvus** — similar to Pinecone; pick one and standardize.
- **Meilisearch / Algolia** — keyword + hybrid; **not** a full replacement for semantic-only search unless you add embeddings on top.

For **“natural language over CVs”**, you need **embeddings + similarity search** — Pinecone (or pgvector) is appropriate.

---

## Where do embeddings “live”?

- **Not** in Laravel’s DB as a raw vector blob for query-time search (you *could* store a JSON copy for debugging; we don’t require it for MVP).
- **Primary search index:** Pinecone **upsert** with:
  - **`id`**: stable string (we store the same value in `candidate_documents.pinecone_vector_id`).
  - **`values`**: float array from OpenAI.
  - **`metadata`**: small JSON-safe fields only (e.g. `organization_id`, `candidate_document_id`) — used for **tenant filtering** at query time.

Relational rows stay in **`candidates`** and **`candidate_documents`**.

---

## How we connect to Pinecone from Laravel

1. **Create an index** in the Pinecone console (serverless is fine). Note:
   - **dimension** must match the embedding model (e.g. `text-embedding-3-small` → **1536**).
   - Copy **`PINECONE_INDEX_HOST`** (data plane host for that index).

2. **`.env`** (see `.env.example`):

   - `PINECONE_API_KEY`
   - `PINECONE_INDEX_HOST` (e.g. `xxxxxx-xxxx.svc.aped-4637-b74a.pinecone.io` — use your console value)
   - `OPENAI_API_KEY` (for embeddings in a later step)
   - Optional: `OPENAI_EMBEDDING_MODEL` (defaults to `text-embedding-3-small`)

3. **Runtime:** inject `App\Services\PineconeVectorService` where needed (queue job after text extraction):

   - Build embedding via OpenAI.
   - Choose a **vector id** convention, e.g. `org-{organization_id}-doc-{document_id}`.
   - Call `upsert($id, $values, $metadata)` with metadata containing `organization_id` (and ids for traceability).
   - Save `pinecone_vector_id`, `embedding_model`, `embedding_dimensions`, set `indexed_at`, `processing_status = ready`.

4. **Search (HR query):**

   - Embed the HR question with the **same** embedding model.
   - `query($vector, $topK, $namespace)` on Pinecone.
   - **Filter** results in PHP or via Pinecone metadata filter so **only the current `organization_id`** is returned.
   - Join Pinecone hit ids back to `candidate_documents` / `candidates` for UI.

**Namespaces:** optional. MVP can use **one index** + **metadata `organization_id` filter**; or **one namespace per organization** — decide once and keep consistent.

---

## Config in this repo

- `config/pinecone.php` — reads env keys.
- `App\Services\PineconeVectorService` — minimal HTTP client for **upsert / query / delete-by-ids**. Adjust paths if Pinecone changes API versions; always verify against current docs.

---

## Next implementation steps (suggested order)

1. **`ProcessCvUploadJob`** (implemented): queued job downloads the CV from object storage to a temp file, extracts text (PDF via `smalot/pdfparser`, DOCX via ZIP/`document.xml`), stores extracted text back to object storage (`extracted_text_path`), then if `OPENAI_API_KEY` **and** Pinecone envs are set, embeds and **upserts** to Pinecone; otherwise marks the row **`ready`** with a note that semantic search needs Pinecone/OpenAI.
2. **HR search** (`POST /dashboard/search`): uses **`HrCandidateSearchService`** — Pinecone + OpenAI when configured (metadata filter `organization_id`); otherwise a simple **filename keyword** match (CV text is not stored in SQL).
3. Run a **queue worker** when `QUEUE_CONNECTION=database` (or Redis): `php artisan queue:work` so uploads process in the background.

This document is the **bridge** between the product spec (`PROJECT_REQUIREMENTS.md`) and the code under `app/Services` + `config/pinecone.php`.
