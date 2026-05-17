<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muzakki', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 13);                      // gampong muzakki terdaftar
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('nama_lengkap', 200);
            $table->string('nik', 20)->nullable()->unique();
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();

            $table->index('kode_desa');
            $table->index('nama_lengkap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muzakki');
    }
};
