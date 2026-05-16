<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\StoreVenueRequest;
use App\Http\Requests\Mtq\Master\UpdateVenueRequest;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VenueController extends Controller
{
    public function index(): Response
    {
        $venues = Venue::orderBy('nama')
            ->get()
            ->map(fn(Venue $v) => [
                'id'        => $v->id,
                'nama'      => $v->nama,
                'alamat'    => $v->alamat,
                'lat'       => $v->lat,
                'lng'       => $v->lng,
                'kapasitas' => $v->kapasitas,
            ]);

        return Inertia::render('Mtq/Master/Venue/Index', [
            'venues' => $venues,
        ]);
    }

    public function store(StoreVenueRequest $request): RedirectResponse
    {
        $venue = Venue::create($request->validated());

        return back()->with('success', "Venue {$venue->nama} berhasil ditambahkan.");
    }

    public function update(UpdateVenueRequest $request, Venue $venue): RedirectResponse
    {
        $venue->update($request->validated());

        return back()->with('success', "Venue {$venue->nama} berhasil diperbarui.");
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $nama = $venue->nama;
        $venue->delete();

        return back()->with('success', "Venue {$nama} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        Venue::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' venue berhasil dihapus.');
    }
}
