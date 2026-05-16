<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('golongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('nama');
            $table->unsignedTinyInteger('min_usia')->nullable();
            $table->unsignedTinyInteger('max_usia')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P', 'LK']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golongans');
    }
};
