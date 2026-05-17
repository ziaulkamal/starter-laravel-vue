<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // kode desa (13-char) untuk admin_gampong — null untuk super_admin & admin_kabupaten
            $table->string('kode_wilayah', 13)
                ->nullable()
                ->after('id');

            $table->foreign('kode_wilayah')
                ->references('kode')
                ->on('wilayah')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kode_wilayah']);
            $table->dropColumn('kode_wilayah');
        });
    }
};
