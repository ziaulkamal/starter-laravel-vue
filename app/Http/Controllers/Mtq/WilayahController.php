<?php

namespace App\Http\Controllers\Mtq;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function provinsi(): JsonResponse
    {
        $data = Wilayah::provinsi()
            ->get(['kode', 'nama'])
            ->map(fn (Wilayah $w) => ['value' => $w->kode, 'label' => $w->nama]);

        return response()->json($data);
    }

    public function kabupaten(Request $request): JsonResponse
    {
        $kode = $request->string('kode')->toString();

        $data = Wilayah::kabupaten($kode ?: null)
            ->get(['kode', 'nama'])
            ->map(fn (Wilayah $w) => ['value' => $w->kode, 'label' => $w->nama]);

        return response()->json($data);
    }

    public function kecamatan(Request $request): JsonResponse
    {
        $kode = $request->string('kode')->toString();

        $data = Wilayah::kecamatan($kode ?: null)
            ->get(['kode', 'nama'])
            ->map(fn (Wilayah $w) => ['value' => $w->kode, 'label' => $w->nama]);

        return response()->json($data);
    }

    public function kelurahan(Request $request): JsonResponse
    {
        $kode = $request->string('kode')->toString();

        $data = Wilayah::kelurahan($kode ?: null)
            ->get(['kode', 'nama'])
            ->map(fn (Wilayah $w) => ['value' => $w->kode, 'label' => $w->nama]);

        return response()->json($data);
    }
}
