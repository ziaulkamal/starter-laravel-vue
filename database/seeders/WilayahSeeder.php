<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('wilayah')->exists()) {
            $this->command->info('WilayahSeeder: data sudah ada, dilewati.');
            return;
        }

        $sqlPath = database_path('wilayah.sql');

        if (! file_exists($sqlPath)) {
            $this->command->error("File tidak ditemukan: {$sqlPath}");
            return;
        }

        $this->command->info('WilayahSeeder: mengimpor data wilayah...');

        $sql = file_get_contents($sqlPath);

        // Hapus baris komentar (-- ...) sebelum parsing
        $sql = preg_replace('/^--.*$/m', '', $sql);

        // Ambil hanya INSERT statements — skip DDL (DROP/CREATE/INDEX)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn (string $s) => stripos($s, 'INSERT') !== false
        );

        DB::beginTransaction();

        try {
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }

            DB::commit();
            $this->command->info('WilayahSeeder: selesai — ' . count($statements) . ' batch INSERT.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('WilayahSeeder gagal: ' . $e->getMessage());
        }
    }
}
