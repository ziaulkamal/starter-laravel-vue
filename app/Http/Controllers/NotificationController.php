<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user
            ->unreadNotifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'created_at' => $n->created_at->diffForHumans(),
                'created_at_ts' => $n->created_at->timestamp,
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items'        => $notifications,
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['ok' => true]);
    }
}
