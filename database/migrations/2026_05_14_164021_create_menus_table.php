<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // Self-referential: null = root item, isi = child dari parent
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('menus')
                  ->nullOnDelete();

            // 'section' = nav-small-cap (pemisah/header grup)
            // 'item'    = link navigasi (bisa punya children)
            $table->enum('type', ['section', 'item'])->default('item');

            $table->string('label');

            // Hanya diisi untuk type = 'item'
            $table->string('icon')->nullable();  // contoh: 'ti ti-users'
            $table->string('href')->nullable();  // contoh: '/users'

            // Urutan tampil di sidebar (ascending)
            $table->unsignedSmallInteger('order_index')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Role access dikelola via pivot table menu_role
            // FK ke roles akan ditambahkan saat role engine diimplementasi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
