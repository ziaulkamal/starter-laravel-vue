<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_zakat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 13);
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();
            $table->enum('jenis_zakat', ['fitrah', 'mal']);
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignId('dikunci_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikunci_pada')->nullable();
            $table->timestamps();

            // Satu periode per gampong per jenis per bulan per tahun
            $table->unique(['kode_desa', 'jenis_zakat', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_zakat');
    }
};
