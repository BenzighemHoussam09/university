<div class="p-6 lg:p-8 max-w-3xl space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-headline font-bold text-primary">الإشعارات</h1>
            <p class="text-sm text-on-surface-variant mt-0.5">
                @php $unreadCount = $notifications->filter(fn($n) => $n->isUnread())->count(); @endphp
                {{ $unreadCount > 0 ? $unreadCount . ' غير مقروءة' : 'جميعها مقروءة' }}
            </p>
        </div>
        @if($unreadCount > 0)
            <button wire:click="markAllRead"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-primary hover:bg-primary-fixed/30 rounded-lg transition-all">
                <span class="material-symbols-outlined text-[16px]">done_all</span>
                تحديد الكل كمقروء
            </button>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl p-16 text-center shadow-sm">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block">notifications_off</span>
            <p class="text-on-surface-variant font-semibold">لا توجد إشعارات</p>
            <p class="text-xs text-on-surface-variant/60 mt-1">ستصلك الإشعارات هنا</p>
        </div>
    @else
        <ul class="space-y-2.5">
            @foreach($notifications as $notification)
            @php
                $iconMap = [
                    'student_account_created' => ['icon' => 'person_add',  'bg' => 'bg-secondary-container text-on-secondary-container'],
                    'exam_reminder'           => ['icon' => 'alarm',        'bg' => 'bg-tertiary-fixed text-on-tertiary-fixed'],
                    'results_available'       => ['icon' => 'bar_chart',    'bg' => 'bg-primary-fixed text-on-primary-fixed-variant'],
                ];
                $meta = $iconMap[$notification->kind] ?? ['icon' => 'notifications', 'bg' => 'bg-surface-container text-on-surface-variant'];
            @endphp
            <li class="bg-surface-container-lowest rounded-xl shadow-sm p-4 flex items-start gap-4
                       {{ $notification->isUnread() ? 'border-r-4 border-primary' : '' }}">
                <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center {{ $meta['bg'] }}">
                    <span class="material-symbols-outlined text-[20px] icon-filled">
                        {{ $meta['icon'] }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-on-surface text-sm leading-snug mb-1">
                        @if($notification->kind === 'student_account_created')
                            مرحباً! تم إنشاء حسابك بنجاح.
                            @if(!empty($notification->payload['login_url']))
                                <a href="{{ $notification->payload['login_url'] }}" class="text-primary hover:underline me-1">تسجيل الدخول</a>
                            @endif
                        @elseif($notification->kind === 'exam_reminder')
                            تذكير: امتحان <strong>{{ $notification->payload['exam_title'] ?? '—' }}</strong> قادم قريباً.
                            @if(!empty($notification->payload['waiting_url']))
                                <a href="{{ $notification->payload['waiting_url'] }}" wire:navigate class="text-primary hover:underline me-1">غرفة الانتظار</a>
                            @endif
                        @elseif($notification->kind === 'results_available')
                            نتيجة امتحان <strong>{{ $notification->payload['exam_title'] ?? '—' }}</strong> متاحة الآن.
                            @if(!empty($notification->payload['results_url']))
                                <a href="{{ $notification->payload['results_url'] }}" wire:navigate class="text-primary hover:underline me-1">عرض النتائج</a>
                            @endif
                        @else
                            {{ $notification->kind }}
                        @endif
                    </p>
                    <p class="text-xs text-on-surface-variant">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex-shrink-0">
                    @if($notification->isUnread())
                        <button wire:click="markRead({{ $notification->id }})"
                                title="تحديد كمقروء"
                                class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary-fixed/20 rounded-lg transition-all">
                            <span class="material-symbols-outlined text-[16px]">mark_email_read</span>
                        </button>
                    @else
                        <span class="w-2 h-2 rounded-full bg-surface-container-high block mt-1"></span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    @endif

</div>
