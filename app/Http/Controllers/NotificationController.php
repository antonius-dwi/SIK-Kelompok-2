<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** JSON untuk dropdown notifikasi di topbar. */
    public function index(Request $request)
    {
        $items = $this->notifications->for($request->user());

        return response()->json([
            'unread' => $items->where('unread', true)->count(),
            'items' => $items->map(fn ($n) => [
                'text' => $n['text'],
                'url' => $n['url'],
                'unread' => $n['unread'],
                'time' => $n['time']->copy()->timezone('Asia/Jakarta')->format('d M · H:i') . ' WIB',
            ]),
        ]);
    }

    /** "Tandai semua dibaca": hanya mengubah status milik pengguna yang login. */
    public function markAllRead(Request $request)
    {
        $this->notifications->markAllRead($request->user());

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}
