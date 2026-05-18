<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->orderByDesc('created_at')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        return view('notifications.index', [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Notification marked as read']);
        }

        $matchId = $notification->data['reference_id'] ?? null;
        $type = (string) ($notification->data['type'] ?? '');
        $matchNotificationTypes = [
            'challenge_created',
            'challenge_accepted',
            'auto_match_found',
            'auto_match_confirmed',
            'auto_match_cancelled',
            'match_cancelled',
        ];

        if ($matchId && in_array($type, $matchNotificationTypes, true)) {
            return redirect()->route('matches.show', $matchId);
        }

        return back();
    }
}
