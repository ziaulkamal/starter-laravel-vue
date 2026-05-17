<?php

namespace App\Http\Controllers\Mtq\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Golongan;
use App\Models\Kafilah;
use App\Models\Kriteria;
use App\Models\Peserta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PesertaGeneratorController extends Controller
{
    private array $maleNames = [
        'Ahmad', 'Muhammad', 'Abdul', 'Hafidz', 'Ilham', 'Fadil', 'Rizki', 'Zainul',
        'Aqil', 'Rayhan', 'Naufal', 'Hakim', 'Fauzan', 'Arif', 'Rasyid', 'Yusuf',
        'Ibrahim', 'Khairul', 'Taufik', 'Hasan', 'Husain', 'Fajri', 'Sofyan', 'Reza',
        'Wahyu', 'Irfan', 'Zainal', 'Syahrul', 'Mukhlis', 'Dani', 'Salman', 'Ridho',
        'Bintang', 'Haikal', 'Zaky', 'Farid', 'Nabil', 'Ghazi', 'Hadi', 'Karim',
    ];

    private array $femaleNames = [
        'Aisyah', 'Fatimah', 'Siti', 'Rahmah', 'Maryam', 'Hafshah', 'Khadijah', 'Aminah',
        'Salma', 'Nabila', 'Zahra', 'Aulia', 'Nadia', 'Rania', 'Zulfa', 'Nisa',
        'Fitri', 'Putri', 'Suci', 'Azzahra', 'Wardah', 'Humaira', 'Raudha', 'Hafizah',
        'Laila', 'Munira', 'Saroh', 'Hidayah', 'Qorina', 'Rahmi', 'Nurul', 'Dina',
        'Syifa', 'Alya', 'Nadya', 'Rifa', 'Hana', 'Isna', 'Zaynab', 'Marwa',
    ];

    private array $lastNames = [
        'Harahap', 'Siregar', 'Daulay', 'Hasibuan', 'Lubis', 'Nasution', 'Batubara',
        'Matondang', 'Ritonga', 'Simbolon', 'Sinaga', 'Panjaitan', 'Sitompul', 'Tanjung',
        'Manurung', 'Simanjuntak', 'Siahaan', 'Ibrahim', 'Ismail', 'Abdullah',
        'Rahman', 'Hamid', 'Yusuf', 'Khalid', 'Zainuddin', 'Fadhil', 'Halim', 'Malik',
        'Saleh', 'Ramadhan', 'Maulana', 'Pratama', 'Putra', 'Saputra', 'Hidayat',
    ];

    private array $cities = [
        'Banda Aceh', 'Sabang', 'Lhokseumawe', 'Langsa', 'Subulussalam', 'Sigli',
        'Bireuen', 'Meulaboh', 'Calang', 'Sinabang', 'Singkil', 'Blangpidie',
        'Blangkejeren', 'Idi Rayeuh', 'Takengon', 'Kualasimpang', 'Meureudu',
        'Suka Makmue', 'Kutacane', 'Tapak Tuan', 'Jeuram', 'Lamno', 'Lhoksukon',
        'Geudong', 'Beutong', 'Jantho', 'Calang', 'Sawang', 'Seunagan', 'Peureulak',
    ];

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jumlah'         => ['required', 'integer', 'min:1', 'max:500'],
            'kafilah_ids'    => ['required', 'array', 'min:1'],
            'kafilah_ids.*'  => ['integer', 'exists:kafilahs,id'],
            'cabang_ids'     => ['required', 'array', 'min:1'],
            'cabang_ids.*'   => ['integer', 'exists:cabangs,id'],
            'golongan_ids'   => ['required', 'array', 'min:1'],
            'golongan_ids.*' => ['integer', 'exists:golongans,id'],
            'kriteria_ids'   => ['nullable', 'array'],
            'kriteria_ids.*' => ['integer', 'exists:kriterias,id'],
        ]);

        $cabangIds = $validated['cabang_ids'];

        // Filter cabang berdasarkan kriteria yang dipilih (opsional)
        if (!empty($validated['kriteria_ids'])) {
            $cabangDenganKriteria = Kriteria::whereIn('id', $validated['kriteria_ids'])
                ->pluck('cabang_id')
                ->unique()
                ->values()
                ->toArray();

            $cabangIds = array_values(array_intersect($cabangIds, $cabangDenganKriteria));

            if (empty($cabangIds)) {
                return response()->json([
                    'error' => 'Tidak ada cabang yang cocok dengan kriteria yang dipilih.',
                ], 422);
            }
        }

        // Golongan valid: harus termasuk dalam cabang yang dipilih
        $golongans = Golongan::whereIn('id', $validated['golongan_ids'])
            ->whereIn('cabang_id', $cabangIds)
            ->get();

        if ($golongans->isEmpty()) {
            return response()->json([
                'error' => 'Tidak ada golongan yang valid untuk kombinasi cabang yang dipilih.',
            ], 422);
        }

        $kafilahs = Kafilah::whereIn('id', $validated['kafilah_ids'])->get();
        $cabangs  = Cabang::whereIn('id', $cabangIds)->get()->keyBy('id');

        // Pre-load existing NIKs untuk uniqueness check
        $existingNiks = Peserta::whereNotNull('nik')->pluck('nik')->flip()->toArray();
        $usedNiks     = [];
        $now          = now()->toDateTimeString();
        $inserts      = [];
        $items        = [];

        for ($i = 0; $i < $validated['jumlah']; $i++) {
            /** @var Golongan $golongan */
            $golongan = $golongans->random();
            /** @var Kafilah $kafilah */
            $kafilah  = $kafilahs->random();
            /** @var Cabang $cabang */
            $cabang   = $cabangs[$golongan->cabang_id];

            $jk = in_array($golongan->jenis_kelamin, ['L', 'P'])
                ? $golongan->jenis_kelamin
                : (rand(0, 1) ? 'L' : 'P');

            $tglLahir = $this->randomTglLahir($golongan);
            $nik      = $this->generateNik($jk, $tglLahir, $kafilah->kode_wilayah, $existingNiks, $usedNiks);
            $usedNiks[$nik] = true;

            $inserts[] = [
                'kafilah_id'    => $kafilah->id,
                'cabang_id'     => $golongan->cabang_id,
                'golongan_id'   => $golongan->id,
                'nama'          => $this->randomNama($jk),
                'nik'           => $nik,
                'jenis_kelamin' => $jk,
                'tgl_lahir'     => $tglLahir,
                'tempat_lahir'  => $this->randomCity(),
                'status'        => 'draft',
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            $items[] = [
                'nama'          => end($inserts)['nama'],
                'jenis_kelamin' => $jk,
                'kafilah_nama'  => $kafilah->nama_kabupaten,
                'cabang_nama'   => $cabang->nama,
                'golongan_nama' => $golongan->nama,
            ];
        }

        Peserta::insert($inserts);

        // Ringkasan per kafilah & cabang
        $byKafilah = [];
        $byCabang  = [];
        foreach ($items as $item) {
            $byKafilah[$item['kafilah_nama']] = ($byKafilah[$item['kafilah_nama']] ?? 0) + 1;
            $byCabang[$item['cabang_nama']]   = ($byCabang[$item['cabang_nama']] ?? 0) + 1;
        }
        arsort($byKafilah);
        arsort($byCabang);

        return response()->json([
            'generated'  => count($items),
            'items'      => $items,
            'by_kafilah' => $byKafilah,
            'by_cabang'  => $byCabang,
        ]);
    }

    private function randomNama(string $jk): string
    {
        $first = $jk === 'L' ? $this->maleNames : $this->femaleNames;
        return $first[array_rand($first)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
    }

    private function randomTglLahir(Golongan $golongan): string
    {
        $today   = Carbon::today();
        $minAge  = $golongan->min_usia ?? 5;
        $maxAge  = $golongan->max_usia ?? 35;
        $minDate = $today->copy()->subYears($maxAge)->subDays(364);
        $maxDate = $today->copy()->subYears($minAge);
        $diff    = max(0, $maxDate->diffInDays($minDate));

        return $minDate->addDays(rand(0, $diff))->format('Y-m-d');
    }

    /**
     * Generate NIK 16-digit sesuai format KTP Indonesia.
     * Format: PPKKCC-DDMMYY-XXXX
     * DD untuk perempuan += 40 (standar BPS)
     */
    private function generateNik(
        string $jk,
        string $tglLahir,
        ?string $kodeWilayah,
        array &$existing,
        array &$used
    ): string {
        $parts   = explode('.', $kodeWilayah ?? '11.01');
        $prov    = str_pad($parts[0] ?? '11', 2, '0', STR_PAD_LEFT);
        $kab     = str_pad($parts[1] ?? '01', 2, '0', STR_PAD_LEFT);
        $kec     = str_pad((string) rand(1, 30), 2, '0', STR_PAD_LEFT);

        $date    = Carbon::parse($tglLahir);
        $dd      = (int) $date->format('d') + ($jk === 'P' ? 40 : 0);
        $datePart = str_pad((string) $dd, 2, '0', STR_PAD_LEFT)
            . $date->format('m')
            . $date->format('y');

        do {
            $seq = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $nik = $prov . $kab . $kec . $datePart . $seq;
        } while (isset($existing[$nik]) || isset($used[$nik]));

        return $nik;
    }

    private function randomCity(): string
    {
        return $this->cities[array_rand($this->cities)];
    }
}
