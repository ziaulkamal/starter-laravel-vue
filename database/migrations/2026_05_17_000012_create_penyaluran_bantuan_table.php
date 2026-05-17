<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyaluran_bantuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_bantuan')->restrictOnDelete();
            $table->foreignId('mustahik_id')->constrained('mustahik')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('program_bantuan')->restrictOnDelete();
            $table->foreignId('disalurkan_oleh')->constrained('users')->restrictOnDelete();
            $table->string('jenis_bantuan', 100);
            $table->decimal('jumlah_bantuan', 15, 2)->nullable();
            $table->string('satuan', 50)->nullable();            // Rp, Kg, unit, dll.
            $table->date('tanggal_penyaluran');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['disalurkan'])->default('disalurkan');
            $table->timestamps();

            $table->index('program_id');
            $table->index('mustahik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyaluran_bantuan');
    }
};
