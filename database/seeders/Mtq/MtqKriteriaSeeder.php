<?php

namespace Database\Seeders\Mtq;

use App\Models\Cabang;
use App\Models\Kriteria;
use Illuminate\Database\Seeder;

class MtqKriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // Format: 'Nama Cabang' => [ ['nama', bobot, min, max], ... ]
        // Total bobot per cabang = 100
        $data = [
            'Tilawah Al-Qur\'an' => [
                ['nama' => 'Tajwid',        'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Lagu/Nagham',   'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Suara/Vokal',   'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Fashahah',      'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Tartil Al-Qur\'an' => [
                ['nama' => 'Tajwid',        'bobot' => 40, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Fashahah',      'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kelancaran',    'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Adab',          'bobot' => 10, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Hifzh Al-Qur\'an' => [
                ['nama' => 'Kelancaran Hafalan', 'bobot' => 40, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Tajwid',             'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Fashahah',           'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Adab',               'bobot' => 10, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Tafsir Al-Qur\'an' => [
                ['nama' => 'Penguasaan Materi', 'bobot' => 40, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Sistematika',       'bobot' => 25, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Bahasa',            'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Penampilan',        'bobot' => 15, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Fahmil Al-Qur\'an' => [
                ['nama' => 'Ketepatan Jawaban', 'bobot' => 50, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kecepatan',         'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kerjasama Tim',     'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Syarhil Al-Qur\'an' => [
                ['nama' => 'Penguasaan Materi', 'bobot' => 35, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Penyampaian',       'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Tilawah Pembuka',   'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kerjasama Tim',     'bobot' => 15, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Khat Al-Qur\'an' => [
                ['nama' => 'Keindahan Tulisan', 'bobot' => 40, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Ketepatan Kaidah',  'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kebersihan',         'bobot' => 20, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kreativitas',        'bobot' => 10, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
            'Makalah Al-Qur\'an' => [
                ['nama' => 'Orisinalitas',      'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Kedalaman Kajian',  'bobot' => 30, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Sistematika',       'bobot' => 25, 'min_nilai' => 0, 'max_nilai' => 100],
                ['nama' => 'Bahasa',            'bobot' => 15, 'min_nilai' => 0, 'max_nilai' => 100],
            ],
        ];

        foreach ($data as $namaCabang => $kriterias) {
            $cabang = Cabang::where('nama', $namaCabang)->first();

            if (! $cabang) {
                continue;
            }

            foreach ($kriterias as $k) {
                Kriteria::firstOrCreate(
                    ['cabang_id' => $cabang->id, 'nama' => $k['nama']],
                    [
                        'cabang_id' => $cabang->id,
                        'nama'      => $k['nama'],
                        'bobot'     => $k['bobot'],
                        'min_nilai' => $k['min_nilai'],
                        'max_nilai' => $k['max_nilai'],
                    ]
                );
            }
        }
    }
}
