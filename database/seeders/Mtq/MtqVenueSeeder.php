<?php

namespace Database\Seeders\Mtq;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class MtqVenueSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            [
                'nama'      => 'Gedung Serba Guna Blangpidie',
                'alamat'    => 'Jl. Iskandar Muda, Blangpidie, Aceh Barat Daya',
                'lat'       => 3.7648,
                'lng'       => 96.8302,
                'kapasitas' => 500,
            ],
            [
                'nama'      => 'Masjid Agung Baiturrahman Blangpidie',
                'alamat'    => 'Jl. Nasional, Blangpidie, Aceh Barat Daya',
                'lat'       => 3.7650,
                'lng'       => 96.8310,
                'kapasitas' => 1000,
            ],
            [
                'nama'      => 'Lapangan Tugu Blangpidie',
                'alamat'    => 'Blangpidie, Kab. Aceh Barat Daya, Aceh',
                'lat'       => 3.7640,
                'lng'       => 96.8290,
                'kapasitas' => 2000,
            ],
            [
                'nama'      => 'Aula Kantor Bupati Aceh Barat Daya',
                'alamat'    => 'Jl. Nasional, Blangpidie, Aceh Barat Daya',
                'lat'       => 3.7660,
                'lng'       => 96.8320,
                'kapasitas' => 300,
            ],
            [
                'nama'      => 'Gedung DPRK Aceh Barat Daya',
                'alamat'    => 'Blangpidie, Kab. Aceh Barat Daya, Aceh',
                'lat'       => null,
                'lng'       => null,
                'kapasitas' => 200,
            ],
        ];

        foreach ($venues as $data) {
            Venue::firstOrCreate(['nama' => $data['nama']], $data);
        }
    }
}
