<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function unread(Request $request): JsonResponse
    {
        $unread = $request->user()->unreadNotifications()->take(5)->get();

        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
            'items' => $unread->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'read_url' => route('notifications.read', $n->id),
            ]),
        ]);
    }

    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->to($notification->data['url'] ?? route('dashboard'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
