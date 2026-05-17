<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'nama_instansi'      => 'Baitul Mal Kabupaten Aceh Barat Daya',
            'alamat_instansi'    => 'Jl. Nasional, Blangpidie, Aceh Barat Daya, Aceh 23681',
            'kepala_nama'        => '',
            'kepala_jabatan'     => 'Kepala Baitul Mal',
            'tahun_aktif'        => (string) date('Y'),
            'logo_path'          => '',
            'kabupaten_kode'     => '11.12',     // Aceh Barat Daya di tabel wilayah
            'kabupaten_nama'     => 'Kabupaten Aceh Barat Daya',
            'provinsi_kode'      => '11',
            'provinsi_nama'      => 'Aceh',
        ];

        foreach ($defaults as $key => $value) {
            SystemConfig::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->info('SystemConfig defaults seeded.');
    }
}
