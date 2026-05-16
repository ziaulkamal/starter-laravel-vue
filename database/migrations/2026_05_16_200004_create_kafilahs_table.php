<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kafilahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kabupaten');
            $table->string('kode_wilayah', 10)->unique();
            $table->string('koordinator_nama')->nullable();
            $table->string('koordinator_kontak')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kafilahs');
    }
};
