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
            $table->foreignId('raw_content_id')->constrained('raw_contents');
            $table->string('hook_propose');
            $table->json('body_points');
            $table->integer('technical_readability_score');
            $table->json('suggested_hashtags');
            $table->text('tone_compliance_justification');
            $table->string('status');
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
