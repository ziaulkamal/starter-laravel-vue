<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('substansi_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('kode_asnaf', 20); // fakir, miskin, amil, muallaf, riqab, gharimin, fisabilillah, ibnu_sabil
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substansi_kategori');
    }
};
