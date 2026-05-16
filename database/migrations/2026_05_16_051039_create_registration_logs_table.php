<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('registered_at');
            $table->enum('action', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_logs');
    }
};
