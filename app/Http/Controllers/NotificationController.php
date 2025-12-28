<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Added this line
class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $notifications = auth()->user()->unreadNotifications()->paginate(10);
        return view('pages.notifications.index', compact('notifications'));
    }
    /**
     * Mark a specific notification as read.
     *
     * @param string $id The ID of the notification to mark as read.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return redirect()->back()->with('success', 'Notification marquée comme lue.');
        }

        return redirect()->back()->with('error', 'Notification introuvable.');
    }


    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }


    public function deleteSelected(Request $request)
    {
        $request->validate([
            'notifications' => 'required|array',
            'notifications.*' => 'exists:notifications,id',
        ]);
        auth()->user()->notifications()->whereIn('id', $request->notifications)->delete();
        return back()->with('success', 'Notifications supprimées avec succès.');
    }
}
