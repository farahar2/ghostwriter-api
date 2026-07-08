<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('agent_conversation_uuid', 36)->nullable()->after('generated_post_id');

            $table->index('agent_conversation_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['agent_conversation_uuid']);
            $table->dropColumn('agent_conversation_uuid');
        });
    }
};
