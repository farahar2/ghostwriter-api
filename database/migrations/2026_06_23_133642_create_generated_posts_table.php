<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('generated_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_content_id')->constrained('raw_contents')->cascadeOnDelete();
            $table->text('hook_propose')->nullable();
            $table->json('body_points')->nullable();
            $table->unsignedTinyInteger('technical_readability_score')->nullable();
            $table->json('suggested_hashtags')->nullable();
            $table->text('tone_compliance_justification')->nullable();
            $table->enum('status', ['draft', 'archived', 'posted'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_posts');
    }
};