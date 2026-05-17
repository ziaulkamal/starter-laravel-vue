<?php

namespace Database\Seeders\Mtq;

use App\Models\Kafilah;
use Illuminate\Database\Seeder;

class MtqKafilahSeeder extends Seeder
{
    public function run(): void
    {
        // Kode wilayah sesuai Kepmendagri No 300.2.2-2138 Tahun 2025
        // Format: XX.XX (panjang 5) — sesuai tabel wilayah, level kabupaten/kota
        $kafilahs = [
            ['nama_kabupaten' => 'Kabupaten Aceh Selatan',    'kode_wilayah' => '11.01'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tenggara',   'kode_wilayah' => '11.02'],
            ['nama_kabupaten' => 'Kabupaten Aceh Timur',      'kode_wilayah' => '11.03'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tengah',     'kode_wilayah' => '11.04'],
            ['nama_kabupaten' => 'Kabupaten Aceh Barat',      'kode_wilayah' => '11.05'],
            ['nama_kabupaten' => 'Kabupaten Aceh Besar',      'kode_wilayah' => '11.06'],
            ['nama_kabupaten' => 'Kabupaten Pidie',           'kode_wilayah' => '11.07'],
            ['nama_kabupaten' => 'Kabupaten Aceh Utara',      'kode_wilayah' => '11.08'],
            ['nama_kabupaten' => 'Kabupaten Simeulue',        'kode_wilayah' => '11.09'],
            ['nama_kabupaten' => 'Kabupaten Aceh Singkil',    'kode_wilayah' => '11.10'],
            ['nama_kabupaten' => 'Kabupaten Bireuen',         'kode_wilayah' => '11.11'],
            ['nama_kabupaten' => 'Kabupaten Aceh Barat Daya', 'kode_wilayah' => '11.12'],
            ['nama_kabupaten' => 'Kabupaten Gayo Lues',       'kode_wilayah' => '11.13'],
            ['nama_kabupaten' => 'Kabupaten Aceh Jaya',       'kode_wilayah' => '11.14'],
            ['nama_kabupaten' => 'Kabupaten Nagan Raya',      'kode_wilayah' => '11.15'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tamiang',    'kode_wilayah' => '11.16'],
            ['nama_kabupaten' => 'Kabupaten Bener Meriah',    'kode_wilayah' => '11.17'],
            ['nama_kabupaten' => 'Kabupaten Pidie Jaya',      'kode_wilayah' => '11.18'],
            ['nama_kabupaten' => 'Kota Banda Aceh',           'kode_wilayah' => '11.71'],
            ['nama_kabupaten' => 'Kota Sabang',               'kode_wilayah' => '11.72'],
            ['nama_kabupaten' => 'Kota Lhokseumawe',          'kode_wilayah' => '11.73'],
            ['nama_kabupaten' => 'Kota Langsa',               'kode_wilayah' => '11.74'],
            ['nama_kabupaten' => 'Kota Subulussalam',         'kode_wilayah' => '11.75'],
        ];

        foreach ($kafilahs as $data) {
            Kafilah::firstOrCreate(['kode_wilayah' => $data['kode_wilayah']], $data);
        }
    }
}
