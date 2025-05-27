<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead(Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        $notification->update(['is_read' => true]);
        
        // Redirect back to the notification's related page if available
        if ($notification->related_url) {
            return redirect($notification->related_url);
        }
        
        return redirect()->back();
    }
    
    public function markAllRead()
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return redirect()->back()
            ->with('success', 'All notifications marked as read.');
    }
}