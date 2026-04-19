# HR AI Assistant SaaS — Project Requirements

> **Purpose:** Single reference document for scope, architecture, and constraints.  
> **Stack note:** Repo currently uses **Laravel 13** + **PHP 8.4+**; requirements below originally mentioned Laravel 11/12 — align implementation with the installed framework version.  
> **Personal / demo context:** This build is intentionally **MVP-first** — enough to **show clients** a real, working slice (tenant + upload + search story), not a full enterprise product on day one.

---

## Goal

Build a **multi-tenant SaaS HR Assistant (MVP)** where each **agency** has an **isolated workspace** and a **public resume submission portal**. The system uses **AI embeddings + vector search (Pinecone)** to match candidate CVs with HR queries semantically.

**MVP mindset:** Prefer the **smallest UI** that proves the idea (e.g. **Blade**, **Livewire**, or **Inertia + simple frontend**). **Vue is optional**, not a requirement — choose whatever ships fastest while staying maintainable.

---

## System Overview

**Laravel** backend (primary) + a **simple** admin / portal UI (stack flexible — see Tech Stack) with two main roles:

### 1. Agency (HR company)

- Can **register** and create an account.
- After registration, the system generates a **unique public URL** per agency.

**Example (path-based):**

`https://domain.com/agency/{agency-slug}`

- This public page is where **candidates upload CVs**.

### 2. HR admin panel

- **Secure login** for agency HR users.
- View all uploaded CVs for **their tenant only**.
- **Search / filter** using **AI-powered semantic search**.
- Support **natural language** queries, e.g.:
  - “Find candidates with AWS experience”
  - “Who has 3+ years Laravel experience?”
  - “Best match for senior backend engineer”

---

## Multi-Tenancy Rules

- Each **agency = one tenant**.
- **All data isolated per agency** — **no cross-tenant access**.
- **Tenant resolution** via one of (product choice):
  - **Subdomain:** `agency-a.domain.com`, `agency-b.domain.com`
  - **OR slug path:** `domain.com/a/agency-a`, `domain.com/a/agency-b`  
  - **OR** (as in examples) `domain.com/agency/{agency-slug}` — **document the chosen pattern in ADR / README when implemented.**

---

## Candidate Flow (Public Portal)

1. Candidate opens **agency URL**.
2. Uploads CV (**PDF / DOC** only — validated).
3. System **extracts text** from CV.
4. CV stored/processed as:
   - **Raw text**
   - **Structured metadata**
   - **Vector embedding** (Pinecone)

---

## AI + Vector System

### Embedding pipeline

1. Extract CV text.  
2. Generate embeddings via **OpenAI Embeddings API**.  
3. Store vectors in **Pinecone** (per-tenant namespace / metadata strategy TBD at implementation).

### Search flow

1. HR submits a **natural language** query.  
2. Convert query → **embedding**.  
3. **Query Pinecone** (scoped to tenant).  
4. Return most relevant CVs, **ranked by similarity score**.

---

## Tech Stack

| Layer | Requirement |
|--------|-------------|
| **Backend** | Laravel (**11/12 in spec; this repo: Laravel 13**), **REST APIs** (clear CRUD layout) |
| **Auth (HR panel)** | **Laravel Sanctum** |
| **Async work** | **Queues** for embedding / heavy processing |
| **Frontend** | **Not fixed.** For MVP, prefer **Blade** and/or **Livewire**, or **Inertia** with any light UI. **Vue only if you explicitly want it** — avoid SPA complexity unless needed. |
| **AI** | OpenAI Embeddings API |
| **Vectors** | Pinecone |
| **Files** | CVs on **S3** or **local** storage (configurable) |

---

## Core Modules (API-oriented)

### Agency module

- `register` / create agency + tenant context  
- `show`, `update`, `destroy` (as per product rules)  
- **Generate unique slug + public URL**

### Candidate module

- `store` — CV upload (tenant-scoped)  
- `index` — list candidates **per tenant**  
- `show` — single candidate / CV detail **per tenant**

### HR search module

- `store` — submit search query → embedding → Pinecone → ranked results  
- `index` — list/history of searches (optional; if in scope)

---

## Security Requirements

- **Mandatory tenant isolation** (queries, files, vectors, cache keys).  
- **Rate limiting** on CV upload endpoints.  
- **Validate file types** — PDF / DOC only (and size limits).  
- **Sanitize** CV text before embedding / storage where applicable.

---

## Queue Jobs

- **`ProcessCVUploadJob`** (name can match codebase conventions):
  - Extract text  
  - Generate embedding  
  - Upsert to Pinecone  
  - Update candidate/CV record status (failed / ready)

---

## Expected Outcomes

The system should support (MVP → demo-ready):

- Multi-tenant SaaS architecture (even if v1 is **slug-only** routes, no wildcards).  
- AI-powered **semantic** search (not keyword-only) for the **happy path demo**.  
- Vector search wired end-to-end (upload → job → Pinecone → HR query → ranked hits).  
- **Clean Laravel** surface: routes/controllers or APIs **as appropriate** — REST is fine where it helps (e.g. search JSON), but **not every screen needs a SPA**.  
- A **credible admin + public portal** UI clients can click through — polish second, **clarity first**.

---

## Constraints

- **No unnecessary extra modules** — stay within described scope.  
- **No over-engineering** — production-ready but **simple**; **MVP over completeness**.  
- Prefer clear boundaries: **tenant context**, **API resources**, **jobs**, **policies**.  
- **Demo goal:** one vertical slice you can record or walk a client through in **a few minutes**.

---

## Bonus (If Time Allows)

- CV **scoring** system  
- **Candidate ranking** per query  
- **AI summary** per CV  
- **Duplicate CV** detection via embedding similarity  

---

## End Goal

A SaaS HR Assistant that (for **you + client demos**):

- **Replaces keyword-only search** with **semantic** search on the demo path  
- **Scales per agency** (multi-tenant) — v1 can prove isolation on **one happy path**  
- Lets HR ask **natural language** questions over the CV database  

Ship something **explainable in one sitting**; expand later if a client buys in.

---

## Optional Next Steps (Design Artifacts)

When ready, produce (as separate docs or tickets):

- **Database schema** (agencies, users, candidates, documents, embeddings metadata, search logs)  
- **Laravel folder architecture** (modules, actions, DTOs — keep aligned with team conventions)  
- **Pinecone integration flow** (index design, namespaces, metadata filters, idempotency)  
- **Full REST API design** (OpenAPI-style list of endpoints, request/response shapes, error codes)

---

## Related Repo State (Snapshot)

- Early DB work may include **`organizations`** + **`users.organization_id` / `role`** — **reconcile naming** (`agency` vs `organization`) when implementing the full spec.  
- Keep this file updated when major scope or stack decisions change.

**Vectors / Pinecone wiring:** see `docs/VECTOR_AND_PINECONE.md`.
