<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /** GET /api/wilayah/kecamatan?parent=11.12 */
    public function kecamatan(Request $request): JsonResponse
    {
        $parent = $request->string('parent', '11.12')->toString(); // default Abdya

        $data = Wilayah::kecamatan($parent)
            ->orderBy('nama')
            ->get(['kode', 'nama']);

        return response()->json($data);
    }

    /** GET /api/wilayah/desa?parent=11.12.01 */
    public function desa(Request $request): JsonResponse
    {
        $parent = $request->string('parent')->toString();

        abort_if(strlen($parent) !== 8, 422, 'Parameter parent harus kode kecamatan (8 karakter).');

        $data = Wilayah::desa($parent)
            ->orderBy('nama')
            ->get(['kode', 'nama']);

        return response()->json($data);
    }

    /** GET /api/wilayah/info?kode=11.12.01.2006 → resolve nama semua level */
    public function info(Request $request): JsonResponse
    {
        $kode = $request->string('kode')->toString();

        abort_if(strlen($kode) !== 13, 422, 'Kode harus kode desa (13 karakter).');

        return response()->json([
            'kode_desa'      => $kode,
            'nama_desa'      => Wilayah::find($kode)?->nama,
            'kode_kecamatan' => $kec = substr($kode, 0, 8),
            'nama_kecamatan' => Wilayah::find($kec)?->nama,
            'kode_kabupaten' => $kab = substr($kode, 0, 5),
            'nama_kabupaten' => Wilayah::find($kab)?->nama,
            'kode_provinsi'  => $prov = substr($kode, 0, 2),
            'nama_provinsi'  => Wilayah::find($prov)?->nama,
        ]);
    }
}
