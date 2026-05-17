<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = database_path('wilayah.sql');

        if (!file_exists($sqlFile)) {
            $this->command->warn('File wilayah.sql tidak ditemukan. Skip seeder.');
            return;
        }

        $this->command->info('Mengimport data wilayah...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('wilayah')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $content = file_get_contents($sqlFile);

        // Normalise line endings
        $content = str_replace("\r\n", "\n", $content);

        // Ambil semua baris VALUES ('kode','nama') dari file
        // Format: ('11','Aceh'), atau ('11.01.01.2018','Pasi Kuala Ba''u'),
        preg_match_all(
            "/\('([^']+)',\s*'((?:[^']|'')*)'\)/",
            $content,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            $this->command->error('Tidak ada data yang ditemukan di wilayah.sql');
            return;
        }

        $rows = [];
        $total = 0;

        foreach ($matches as $row) {
            $rows[] = [
                'kode' => $row[1],
                'nama' => str_replace("''", "'", $row[2]),
            ];

            if (count($rows) >= 500) {
                DB::table('wilayah')->insert($rows);
                $total += count($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('wilayah')->insert($rows);
            $total += count($rows);
        }

        $this->command->info("Berhasil import {$total} wilayah.");
    }
}
