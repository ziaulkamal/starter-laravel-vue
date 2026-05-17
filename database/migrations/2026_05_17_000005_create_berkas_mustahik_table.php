<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_mustahik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mustahik_id')->constrained('mustahik')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->enum('jenis_berkas', [
                'ktp',
                'kk',
                'surat_keterangan',
                'foto_rumah',
                'buku_rekening',
                'lainnya',
            ]);
            $table->string('nama_file', 255);
            $table->string('path_file', 500);
            $table->unsignedInteger('ukuran_file');         // bytes
            $table->string('mime_type', 50);
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index('mustahik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_mustahik');
    }
};
