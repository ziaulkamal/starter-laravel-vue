<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zakat_records', function (Blueprint $table) {
            $table->id();

            // Gampong yang mencatat
            $table->string('kode_desa', 13);
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();

            // Muzakki terdaftar (nullable = input manual)
            $table->foreignId('muzakki_id')->nullable()->constrained('muzakki')->nullOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();

            $table->enum('jenis_zakat', ['fitrah', 'mal']);

            // Data muzakki manual (jika muzakki_id null)
            $table->string('nama_muzakki_manual', 150)->nullable();

            // Asal muzakki (simpan kode, tampil nama)
            $table->string('kode_kecamatan_muzakki', 8)->nullable();
            $table->string('kode_desa_muzakki', 13)->nullable();
            $table->foreign('kode_kecamatan_muzakki')->references('kode')->on('wilayah')->nullOnDelete();
            $table->foreign('kode_desa_muzakki')->references('kode')->on('wilayah')->nullOnDelete();

            // Zakat fitrah
            $table->unsignedTinyInteger('jumlah_jiwa')->default(1);
            $table->decimal('jumlah_beras_kg', 10, 2)->default(0.00);

            // Zakat fitrah & mal
            $table->decimal('jumlah_uang', 15, 2)->default(0.00);

            // Zakat mal
            $table->string('jenis_harta', 100)->nullable();

            // Periode
            $table->date('tanggal_penerimaan');
            $table->unsignedTinyInteger('bulan');               // 1-12
            $table->unsignedSmallInteger('tahun');
            $table->string('tahun_hijriah', 10)->nullable();    // e.g. "1446 H"

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['kode_desa', 'jenis_zakat', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zakat_records');
    }
};
