<?php

namespace Database\Seeders\Mtq;

use Illuminate\Database\Seeder;

class MtqMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MtqKafilahSeeder::class,        // 23 kafilah se-Aceh
            MtqCabangGolonganSeeder::class, // 8 cabang + golongan-nya
            MtqKriteriaSeeder::class,       // kriteria penilaian per cabang
            MtqVenueSeeder::class,          // 5 venue placeholder
        ]);
    }
}
