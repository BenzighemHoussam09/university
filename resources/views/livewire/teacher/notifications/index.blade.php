<div class="p-6 lg:p-8 max-w-4xl space-y-6"
     x-data="{ activeFilter: 'all' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">التنبيهات</h1>
            <p class="text-on-surface-variant mt-1 text-sm">
                @php $unreadCount = $notifications->filter(fn($n) => $n->isUnread())->count(); @endphp
                @if ($unreadCount > 0)
                    لديك {{ $unreadCount }} تنبيه غير مقروء.
                @else
                    جميع التنبيهات مقروءة.
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <button
                wire:click="markAllRead"
                class="btn-gradient text-on-primary px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity flex items-center gap-2 self-start sm:self-auto">
                <span class="material-symbols-outlined text-[18px] icon-filled">done_all</span>
                تحديد الكل كمقروء
            </button>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="flex items-center gap-2 flex-wrap">
        <button @click="activeFilter = 'all'"
                :class="activeFilter === 'all'
                    ? 'bg-primary text-on-primary shadow-md'
                    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
            الكل
        </button>
        <button @click="activeFilter = 'student_account_created'"
                :class="activeFilter === 'student_account_created'
                    ? 'bg-primary text-on-primary shadow-md'
                    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
            الطلاب
        </button>
        <button @click="activeFilter = 'exam_reminder'"
                :class="activeFilter === 'exam_reminder'
                    ? 'bg-primary text-on-primary shadow-md'
                    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
            الامتحانات
        </button>
        <button @click="activeFilter = 'results_available'"
                :class="activeFilter === 'results_available'
                    ? 'bg-primary text-on-primary shadow-md'
                    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
            النتائج
        </button>
    </div>

    {{-- Notification list --}}
    @if ($notifications->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-24 h-24 mb-5 bg-surface-container rounded-full flex items-center justify-center text-outline-variant">
                <span class="material-symbols-outlined text-5xl">notifications_off</span>
            </div>
            <h4 class="text-lg font-bold text-on-surface mb-1">لا توجد تنبيهات</h4>
            <p class="text-on-surface-variant text-sm">أنت على اطلاع دائم بكل شيء!</p>
        </div>
    @else
        <div class="space-y-3">

            @foreach ($notifications as $notification)
                @php
                    $iconMap = [
                        'student_account_created' => ['icon' => 'person_add',           'bg' => 'bg-secondary-fixed',  'fg' => 'text-on-secondary-fixed-variant'],
                        'exam_reminder'           => ['icon' => 'schedule',              'bg' => 'bg-primary-fixed',    'fg' => 'text-on-primary-fixed-variant'],
                        'results_available'       => ['icon' => 'assignment_turned_in',  'bg' => 'bg-tertiary-fixed',   'fg' => 'text-on-tertiary-fixed-variant'],
                    ];
                    $style = $iconMap[$notification->kind] ?? ['icon' => 'notifications', 'bg' => 'bg-surface-container', 'fg' => 'text-on-surface-variant'];

                    $borderColor = match(true) {
                        $notification->kind === 'student_account_created' => 'border-secondary-container',
                        $notification->kind === 'exam_reminder'           => 'border-primary',
                        $notification->kind === 'results_available'       => 'border-tertiary-container',
                        default                                           => 'border-transparent',
                    };

                    $titleMap = [
                        'student_account_created' => 'طالب جديد مضاف',
                        'exam_reminder'           => 'تذكير امتحان',
                        'results_available'       => 'النتائج متاحة',
                    ];
                    $title = $titleMap[$notification->kind] ?? $notification->kind;
                @endphp

                <div x-show="activeFilter === 'all' || activeFilter === '{{ $notification->kind }}'"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="group {{ $notification->isUnread() ? 'bg-surface-container-lowest' : 'bg-surface-container-low/50 opacity-80' }}
                            p-5 rounded-xl transition-all duration-300 border-r-4 {{ $notification->isUnread() ? $borderColor : 'border-transparent' }}
                            hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)]">

                    <div class="flex items-start gap-4">

                        {{-- Icon --}}
                        <div class="w-12 h-12 rounded-xl {{ $style['bg'] }} flex items-center justify-center {{ $style['fg'] }} shrink-0">
                            <span class="material-symbols-outlined text-[20px] icon-filled"
                                 >{{ $style['icon'] }}</span>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-bold text-base {{ $notification->isUnread() ? 'text-primary' : 'text-on-surface' }}">
                                    {{ $title }}
                                </h3>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-xs font-medium text-outline">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if ($notification->isUnread())
                                        <div class="w-2.5 h-2.5 bg-primary rounded-full shrink-0"></div>
                                    @endif
                                </div>
                            </div>

                            @if (!empty($notification->payload['exam_title']))
                                <p class="text-on-surface-variant text-sm leading-relaxed mb-3">
                                    {{ $notification->payload['exam_title'] }}
                                </p>
                            @endif

                            {{-- Actions --}}
                            @if ($notification->isUnread())
                                <div class="flex items-center gap-3 mt-3">
                                    <button
                                        wire:click="markRead({{ $notification->id }})"
                                        class="px-4 py-1.5 text-xs font-bold text-primary bg-primary/5 rounded-md hover:bg-primary/10 transition-colors">
                                        تمت القراءة
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            @endforeach

            {{-- Empty filter state --}}
            <div x-show="!document.querySelector('[x-show]:not([style*=\'display: none\'])') || [...document.querySelectorAll('[x-show]')].every(el => el.style.display === 'none')"
                 class="hidden py-12 text-center">
                <span class="material-symbols-outlined text-4xl text-outline-variant">filter_list_off</span>
                <p class="text-on-surface-variant text-sm mt-2">لا توجد تنبيهات في هذا التصنيف.</p>
            </div>

        </div>
    @endif

</div>
