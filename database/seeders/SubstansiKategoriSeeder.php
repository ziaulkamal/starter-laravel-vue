<?php

namespace Database\Seeders;

use App\Models\SubstansiKategori;
use Illuminate\Database\Seeder;

class SubstansiKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $asnaf = [
            [
                'nama'       => 'Fakir',
                'kode_asnaf' => 'fakir',
                'keterangan' => 'Orang yang tidak mempunyai harta atau usaha yang dapat menjamin 50% kebutuhan hidupnya.',
            ],
            [
                'nama'       => 'Miskin',
                'kode_asnaf' => 'miskin',
                'keterangan' => 'Orang yang mempunyai harta atau usaha yang dapat menjamin 50% kebutuhan hidupnya atau lebih, tetapi tidak mencukupi 100%.',
            ],
            [
                'nama'       => 'Amil',
                'kode_asnaf' => 'amil',
                'keterangan' => 'Orang-orang yang ditunjuk untuk mengumpulkan, menyimpan, dan mendistribusikan zakat.',
            ],
            [
                'nama'       => 'Muallaf',
                'kode_asnaf' => 'muallaf',
                'keterangan' => 'Orang yang baru masuk Islam dan membutuhkan bantuan untuk menguatkan keimanannya.',
            ],
            [
                'nama'       => 'Riqab (Memerdekakan Budak)',
                'kode_asnaf' => 'riqab',
                'keterangan' => 'Orang yang dalam perbudakan atau ikatan yang memerlukan bantuan untuk membebaskan diri.',
            ],
            [
                'nama'       => 'Gharimin (Orang Berutang)',
                'kode_asnaf' => 'gharimin',
                'keterangan' => 'Orang yang mempunyai utang untuk kebutuhan hidup yang halal dan tidak mampu membayarnya.',
            ],
            [
                'nama'       => 'Fisabilillah',
                'kode_asnaf' => 'fisabilillah',
                'keterangan' => 'Orang yang berjuang di jalan Allah untuk kepentingan agama Islam.',
            ],
            [
                'nama'       => 'Ibnu Sabil (Musafir)',
                'kode_asnaf' => 'ibnu_sabil',
                'keterangan' => 'Orang yang dalam perjalanan (musafir) yang kehabisan bekal dan tidak mampu meneruskan perjalanannya.',
            ],
        ];

        foreach ($asnaf as $data) {
            SubstansiKategori::firstOrCreate(
                ['kode_asnaf' => $data['kode_asnaf']],
                array_merge($data, ['is_active' => true])
            );
        }

        $this->command->info('SubstansiKategori (8 asnaf) seeded.');
    }
}
