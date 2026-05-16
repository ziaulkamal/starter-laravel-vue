<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PendingRegistrationController extends Controller
{
    public function index(): Response
    {
        $items = PendingRegistration::latest('created_at')
            ->get()
            ->map(fn (PendingRegistration $p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'email'         => $p->email,
                'phone'         => $p->phone ? '+' . $p->phone : null,
                'ip_address'    => $p->ip_address,
                'status'        => $p->status,
                'notes'         => $p->notes,
                'registered_at' => $p->created_at->format('d M Y, H:i'),
            ])
            ->toArray();

        return Inertia::render('Settings/PendingRegistrations/Index', [
            'items' => $items,
        ]);
    }

    public function approve(PendingRegistration $pending): RedirectResponse
    {
        if ($pending->status !== 'pending') {
            return back()->with('error', 'Pendaftaran ini sudah diproses.');
        }

        User::create([
            'name'     => $pending->name,
            'email'    => $pending->email,
            'phone'    => $pending->phone,
            'password' => Hash::make($pending->password),
        ]);

        $pending->update(['status' => 'approved']);

        return back()->with('success', "Pendaftaran {$pending->name} berhasil disetujui.");
    }

    public function reject(Request $request, PendingRegistration $pending): RedirectResponse
    {
        if ($pending->status !== 'pending') {
            return back()->with('error', 'Pendaftaran ini sudah diproses.');
        }

        $pending->update([
            'status' => 'rejected',
            'notes'  => $request->input('notes'),
        ]);

        return back()->with('success', "Pendaftaran {$pending->name} ditolak.");
    }
}
