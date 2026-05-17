<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_bantuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program', 200);
            $table->unsignedSmallInteger('tahun');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'tutup', 'arsip'])->default('aktif');
            $table->unsignedInteger('target_penerima')->nullable();
            $table->decimal('alokasi_dana', 15, 2)->nullable();
            $table->date('tgl_mulai_pengajuan')->nullable();
            $table->date('tgl_tutup_pengajuan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tahun', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_bantuan');
    }
};
