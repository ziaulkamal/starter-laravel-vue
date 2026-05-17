<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mustahik', function (Blueprint $table) {
            $table->timestamp('survey_filled_at')->nullable()->after('catatan');
            $table->foreignId('survey_filled_by')->nullable()->after('survey_filled_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mustahik', function (Blueprint $table) {
            $table->dropForeign(['survey_filled_by']);
            $table->dropColumn(['survey_filled_at', 'survey_filled_by']);
        });
    }
};
