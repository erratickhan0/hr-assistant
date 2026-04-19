<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_documents', function (Blueprint $table): void {
            $table->string('extracted_text_path')->nullable()->after('path');
            $table->dropColumn('extracted_text');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_documents', function (Blueprint $table): void {
            $table->longText('extracted_text')->nullable()->after('size_bytes');
            $table->dropColumn('extracted_text_path');
        });
    }
};
