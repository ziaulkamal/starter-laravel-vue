<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\RegistrationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
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

        RegistrationLog::create([
            'name'          => $pending->name,
            'email'         => $pending->email,
            'phone'         => $pending->phone,
            'ip_address'    => $pending->ip_address,
            'registered_at' => $pending->created_at,
            'action'        => 'approved',
            'notes'         => null,
            'processed_by'  => Auth::id(),
            'processed_at'  => now(),
        ]);

        DatabaseNotification::where('data->pending_id', $pending->id)->delete();

        $name = $pending->name;
        $pending->delete();

        return back()->with('success', "Pendaftaran {$name} berhasil disetujui.");
    }

    public function reject(Request $request, PendingRegistration $pending): RedirectResponse
    {
        if ($pending->status !== 'pending') {
            return back()->with('error', 'Pendaftaran ini sudah diproses.');
        }

        RegistrationLog::create([
            'name'          => $pending->name,
            'email'         => $pending->email,
            'phone'         => $pending->phone,
            'ip_address'    => $pending->ip_address,
            'registered_at' => $pending->created_at,
            'action'        => 'rejected',
            'notes'         => $request->input('notes'),
            'processed_by'  => Auth::id(),
            'processed_at'  => now(),
        ]);

        DatabaseNotification::where('data->pending_id', $pending->id)->delete();

        $name = $pending->name;
        $pending->delete();

        return back()->with('success', "Pendaftaran {$name} ditolak.");
    }
}
