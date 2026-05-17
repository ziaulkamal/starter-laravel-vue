<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_bantuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mustahik_id')->constrained('mustahik')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('program_bantuan')->restrictOnDelete();
            $table->foreignId('substansi_id')->nullable()->constrained('substansi_kategori')->nullOnDelete();

            // kode_desa = gampong asal mustahik (denormalized untuk efisiensi filter)
            $table->string('kode_desa', 13);
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();

            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'draft',
                'diajukan',
                'sedang_diverifikasi',
                'disetujui',
                'ditolak',
            ])->default('draft');

            $table->text('catatan_pengaju')->nullable();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();

            // Satu mustahik hanya bisa diajukan sekali per program
            $table->unique(['mustahik_id', 'program_id']);

            $table->index('status');
            $table->index('program_id');
            $table->index('kode_desa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bantuan');
    }
};
