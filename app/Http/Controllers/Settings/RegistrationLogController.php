<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RegistrationLog;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationLogController extends Controller
{
    public function index(): Response
    {
        $logs = RegistrationLog::with('processor:id,name')
            ->latest()
            ->get()
            ->map(fn (RegistrationLog $log) => [
                'id'            => $log->id,
                'name'          => $log->name,
                'email'         => $log->email,
                'phone'         => $log->phone ? '+' . $log->phone : null,
                'ip_address'    => $log->ip_address,
                'registered_at' => $log->registered_at->format('d M Y, H:i'),
                'action'        => $log->action,
                'notes'         => $log->notes,
                'processed_by'  => $log->processor?->name ?? '—',
                'processed_at'  => $log->processed_at->format('d M Y, H:i'),
            ])
            ->toArray();

        return Inertia::render('Settings/RegistrationLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
