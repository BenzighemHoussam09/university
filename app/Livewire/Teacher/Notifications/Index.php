<?php

namespace App\Livewire\Teacher\Notifications;

use App\Models\InPlatformNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Index extends Component
{
    public function markRead(int $notificationId): void
    {
        $notification = InPlatformNotification::withoutGlobalScopes()
            ->where('recipient_type', 'teacher')
            ->where('recipient_id', Auth::guard('teacher')->id())
            ->findOrFail($notificationId);

        $notification->markRead();
    }

    public function markAllRead(): void
    {
        InPlatformNotification::withoutGlobalScopes()
            ->where('recipient_type', 'teacher')
            ->where('recipient_id', Auth::guard('teacher')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $notifications = InPlatformNotification::withoutGlobalScopes()
            ->where('recipient_type', 'teacher')
            ->where('recipient_id', Auth::guard('teacher')->id())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.teacher.notifications.index', compact('notifications'));
    }
}
