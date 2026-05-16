<?php

namespace Database\Seeders\Mtq;

use App\Models\Kafilah;
use Illuminate\Database\Seeder;

class MtqKafilahSeeder extends Seeder
{
    public function run(): void
    {
        $kafilahs = [
            ['nama_kabupaten' => 'Kabupaten Simeulue',        'kode_wilayah' => '1101'],
            ['nama_kabupaten' => 'Kabupaten Aceh Singkil',    'kode_wilayah' => '1102'],
            ['nama_kabupaten' => 'Kabupaten Aceh Selatan',    'kode_wilayah' => '1103'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tenggara',   'kode_wilayah' => '1104'],
            ['nama_kabupaten' => 'Kabupaten Aceh Timur',      'kode_wilayah' => '1105'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tengah',     'kode_wilayah' => '1106'],
            ['nama_kabupaten' => 'Kabupaten Aceh Barat',      'kode_wilayah' => '1107'],
            ['nama_kabupaten' => 'Kabupaten Aceh Besar',      'kode_wilayah' => '1108'],
            ['nama_kabupaten' => 'Kabupaten Pidie',           'kode_wilayah' => '1109'],
            ['nama_kabupaten' => 'Kabupaten Bireuen',         'kode_wilayah' => '1110'],
            ['nama_kabupaten' => 'Kabupaten Aceh Utara',      'kode_wilayah' => '1111'],
            ['nama_kabupaten' => 'Kabupaten Aceh Barat Daya', 'kode_wilayah' => '1112'],
            ['nama_kabupaten' => 'Kabupaten Gayo Lues',       'kode_wilayah' => '1113'],
            ['nama_kabupaten' => 'Kabupaten Aceh Tamiang',    'kode_wilayah' => '1114'],
            ['nama_kabupaten' => 'Kabupaten Nagan Raya',      'kode_wilayah' => '1115'],
            ['nama_kabupaten' => 'Kabupaten Aceh Jaya',       'kode_wilayah' => '1116'],
            ['nama_kabupaten' => 'Kabupaten Bener Meriah',    'kode_wilayah' => '1117'],
            ['nama_kabupaten' => 'Kabupaten Pidie Jaya',      'kode_wilayah' => '1118'],
            ['nama_kabupaten' => 'Kota Banda Aceh',           'kode_wilayah' => '1171'],
            ['nama_kabupaten' => 'Kota Sabang',               'kode_wilayah' => '1172'],
            ['nama_kabupaten' => 'Kota Langsa',               'kode_wilayah' => '1173'],
            ['nama_kabupaten' => 'Kota Lhokseumawe',          'kode_wilayah' => '1174'],
            ['nama_kabupaten' => 'Kota Subulussalam',         'kode_wilayah' => '1175'],
        ];

        foreach ($kafilahs as $data) {
            Kafilah::firstOrCreate(['kode_wilayah' => $data['kode_wilayah']], $data);
        }
    }
}
