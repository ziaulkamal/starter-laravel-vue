<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mustahik', function (Blueprint $table) {
            $table->id();

            // Wilayah (kode disimpan, nama di-resolve via wilayah table)
            $table->string('kode_provinsi', 2)->nullable();
            $table->string('kode_kabupaten', 5)->nullable();
            $table->string('kode_kecamatan', 8)->nullable();
            $table->string('kode_desa', 13);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            // Identitas
            $table->string('nama_lengkap', 200);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string('nik', 20)->nullable()->unique();

            // Alamat
            $table->string('alamat_gampong', 150);
            $table->string('alamat_dusun', 150)->nullable();

            // Kontak & Rekening
            $table->string('no_hp', 20)->nullable();
            $table->string('pemilik_rekening', 150)->nullable();
            $table->string('nomor_rekening', 50)->nullable();

            // Pekerjaan & Status Pernikahan
            $table->string('pekerjaan', 100);
            $table->enum('status_pernikahan', ['kawin', 'belum_kawin', 'janda', 'duda']);

            // Kondisi Keluarga
            $table->enum('jumlah_anggota', ['tidak_ada', '1-2', '2-5', 'lebih_5']);
            $table->enum('jumlah_tanggungan', ['tidak_ada', '1-2', '2-5', 'lebih_5']);

            // Kondisi Ekonomi
            $table->enum('range_penghasilan', [
                'tidak_ada',
                'kurang_1500000',
                '1500000_2500000',
                'lebih_2500000',
            ]);
            $table->enum('status_pencari_nafkah', ['utama', 'sampingan', 'tidak_mencari']);

            // Status mustahik
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FK ke wilayah
            $table->foreign('kode_provinsi')->references('kode')->on('wilayah')->nullOnDelete();
            $table->foreign('kode_kabupaten')->references('kode')->on('wilayah')->nullOnDelete();
            $table->foreign('kode_kecamatan')->references('kode')->on('wilayah')->nullOnDelete();
            $table->foreign('kode_desa')->references('kode')->on('wilayah')->restrictOnDelete();

            // Index untuk filter & search
            $table->index('kode_desa');
            $table->index('kode_kecamatan');
            $table->index('status');
            $table->index('nama_lengkap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mustahik');
    }
};
