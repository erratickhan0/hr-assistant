<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('disk', 32)->default('local');
            $table->string('path');
            $table->string('mime', 127);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->longText('extracted_text')->nullable();
            $table->string('processing_status', 32)->default('pending')->index();
            /** Pinecone vector row id (we choose it on upsert; often prefixed by org for one global index). */
            $table->string('pinecone_vector_id')->nullable()->unique();
            $table->string('embedding_model', 64)->nullable();
            $table->unsignedSmallInteger('embedding_dimensions')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['candidate_id', 'processing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_documents');
    }
};
