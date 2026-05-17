<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::drop('pengajuan_bantuan');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Schema::create('pengajuan_bantuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mustahik_id')->constrained('mustahik')->cascadeOnDelete();
            $table->foreignId('substansi_kategori_id')->constrained('substansi_kategori')->restrictOnDelete();

            // Denormalized untuk filter efisien tanpa JOIN
            $table->string('kode_desa', 13);
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();

            $table->smallInteger('tahun')->unsigned();

            $table->foreignId('diajukan_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'diajukan',   // gampong submit
                'ditolak',    // kabupaten tolak
                'sanggah',    // gampong sanggah → kembali ke antrian
                'disetujui',  // kabupaten setuju, menunggu survey
                'disurvey',   // sudah disurvey (data rekening+kondisi diisi)
                'selesai',    // proses selesai
            ])->default('diajukan');

            $table->text('catatan_pengaju')->nullable();       // catatan gampong saat ajukan/sanggah
            $table->text('alasan_penolakan')->nullable();      // kabupaten → dikirim ke gampong
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_keputusan')->nullable();

            $table->timestamps();

            // 1 mustahik hanya bisa punya 1 pengajuan aktif per tahun
            $table->unique(['mustahik_id', 'tahun']);
            $table->index('status');
            $table->index('kode_desa');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bantuan');
    }
};
