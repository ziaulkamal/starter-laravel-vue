<?php

namespace Database\Seeders\Mtq;

use App\Models\Cabang;
use App\Models\Golongan;
use Illuminate\Database\Seeder;

class MtqCabangGolonganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'cabang' => ['nama' => 'Tilawah Al-Qur\'an', 'deskripsi' => 'Cabang membaca Al-Qur\'an dengan lagu'],
                'golongans' => [
                    ['nama' => 'Anak-Anak Putra',  'min_usia' => 8,  'max_usia' => 12, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Anak-Anak Putri',  'min_usia' => 8,  'max_usia' => 12, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Remaja Putra',      'min_usia' => 13, 'max_usia' => 17, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Remaja Putri',      'min_usia' => 13, 'max_usia' => 17, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Dewasa Putra',      'min_usia' => 18, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Dewasa Putri',      'min_usia' => 18, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Tartil Al-Qur\'an', 'deskripsi' => 'Cabang membaca Al-Qur\'an dengan tartil'],
                'golongans' => [
                    ['nama' => 'Putra', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Putri', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Hifzh Al-Qur\'an', 'deskripsi' => 'Cabang hafalan Al-Qur\'an'],
                'golongans' => [
                    ['nama' => '1 Juz & Tilawah Putra',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => '1 Juz & Tilawah Putri',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => '5 Juz Putra',             'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => '5 Juz Putri',             'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => '10 Juz Putra',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => '10 Juz Putri',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => '20 Juz Putra',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => '20 Juz Putri',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => '30 Juz Putra',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => '30 Juz Putri',            'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Tafsir Al-Qur\'an', 'deskripsi' => 'Cabang penafsiran Al-Qur\'an'],
                'golongans' => [
                    ['nama' => 'Bahasa Arab Putra',       'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Bahasa Arab Putri',       'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Bahasa Indonesia Putra',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Bahasa Indonesia Putri',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Bahasa Inggris Putra',    'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Bahasa Inggris Putri',    'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Fahmil Al-Qur\'an', 'deskripsi' => 'Cabang pemahaman Al-Qur\'an (cerdas cermat)'],
                'golongans' => [
                    ['nama' => 'Regu Campuran', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'LK'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Syarhil Al-Qur\'an', 'deskripsi' => 'Cabang syarahan Al-Qur\'an'],
                'golongans' => [
                    ['nama' => 'Regu Campuran', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'LK'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Khat Al-Qur\'an', 'deskripsi' => 'Cabang kaligrafi Al-Qur\'an'],
                'golongans' => [
                    ['nama' => 'Naskah Putra',       'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Naskah Putri',       'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Dekorasi Putra',     'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Dekorasi Putri',     'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                    ['nama' => 'Kontemporer Putra',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Kontemporer Putri',  'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
            [
                'cabang' => ['nama' => 'Makalah Al-Qur\'an', 'deskripsi' => 'Cabang karya tulis ilmiah Al-Qur\'an'],
                'golongans' => [
                    ['nama' => 'Putra', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'L'],
                    ['nama' => 'Putri', 'min_usia' => null, 'max_usia' => null, 'jenis_kelamin' => 'P'],
                ],
            ],
        ];

        foreach ($data as $item) {
            $cabang = Cabang::firstOrCreate(
                ['nama' => $item['cabang']['nama']],
                array_merge($item['cabang'], ['is_active' => true])
            );

            foreach ($item['golongans'] as $golonganData) {
                Golongan::firstOrCreate(
                    ['cabang_id' => $cabang->id, 'nama' => $golonganData['nama']],
                    array_merge($golonganData, ['cabang_id' => $cabang->id])
                );
            }
        }
    }
}
