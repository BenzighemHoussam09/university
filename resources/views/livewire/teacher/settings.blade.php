<div class="p-6 lg:p-8 max-w-2xl space-y-6">

    {{-- Page heading --}}
    <div>
        <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">إعدادات التقييم</h1>
        <p class="text-on-surface-variant mt-1 text-sm">
            حدّد الحد الأقصى لكل مكوّن تقييمي. مجموع المكوّنات الأربعة يجب أن يساوي <strong class="text-on-surface">20</strong>.
        </p>
    </div>

    {{-- Alerts --}}
    @if ($successMessage)
        <div class="flex items-center gap-3 bg-primary-fixed/30 border border-primary/20 text-primary rounded-xl px-4 py-3 text-sm font-medium">
            <span class="material-symbols-outlined text-[18px] icon-filled">check_circle</span>
            {{ $successMessage }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="flex items-center gap-3 bg-error-container border border-error/20 text-error rounded-xl px-4 py-3 text-sm font-medium">
            <span class="material-symbols-outlined text-[18px] icon-filled">error</span>
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Settings form --}}
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-ambient">

        <form wire:submit="save" class="space-y-5">

            {{-- Exam Max --}}
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="examMax" class="text-sm font-semibold text-on-surface">
                        نقاط الامتحان
                    </label>
                    <span class="text-xs text-on-surface-variant">من 0 إلى 20</span>
                </div>
                <input
                    type="number"
                    wire:model.live="examMax"
                    id="examMax"
                    min="0"
                    max="20"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('examMax') border-error focus:ring-error/30 @enderror"
                >
                @error('examMax')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Personal Work Max --}}
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="personalWorkMax" class="text-sm font-semibold text-on-surface">
                        الأعمال الشخصية
                    </label>
                    <span class="text-xs text-on-surface-variant">من 0 إلى 20</span>
                </div>
                <input
                    type="number"
                    wire:model.live="personalWorkMax"
                    id="personalWorkMax"
                    min="0"
                    max="20"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('personalWorkMax') border-error focus:ring-error/30 @enderror"
                >
                @error('personalWorkMax')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Attendance Max --}}
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="attendanceMax" class="text-sm font-semibold text-on-surface">
                        الحضور
                    </label>
                    <span class="text-xs text-on-surface-variant">من 0 إلى 20</span>
                </div>
                <input
                    type="number"
                    wire:model.live="attendanceMax"
                    id="attendanceMax"
                    min="0"
                    max="20"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('attendanceMax') border-error focus:ring-error/30 @enderror"
                >
                @error('attendanceMax')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Participation Max --}}
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="participationMax" class="text-sm font-semibold text-on-surface">
                        المشاركة
                    </label>
                    <span class="text-xs text-on-surface-variant">من 0 إلى 20</span>
                </div>
                <input
                    type="number"
                    wire:model.live="participationMax"
                    id="participationMax"
                    min="0"
                    max="20"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('participationMax') border-error focus:ring-error/30 @enderror"
                >
                @error('participationMax')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Live sum display + submit --}}
            <div class="pt-3 border-t border-outline-variant/20 flex items-center justify-between gap-4">

                <div class="flex items-center gap-3">
                    <span class="text-sm text-on-surface-variant">المجموع:</span>
                    <span class="text-2xl font-extrabold font-headline
                                 {{ $this->componentSum === 20 ? 'text-primary' : 'text-error' }}">
                        {{ $this->componentSum }}
                    </span>
                    <span class="text-sm text-on-surface-variant">/ 20</span>
                    @if ($this->componentSum !== 20)
                        <span class="flex items-center gap-1 text-xs font-semibold text-error bg-error-container px-2 py-1 rounded-full">
                            <span class="material-symbols-outlined text-[14px]">warning</span>
                            يجب أن يساوي 20
                        </span>
                    @else
                        <span class="flex items-center gap-1 text-xs font-semibold text-primary bg-primary-fixed px-2 py-1 rounded-full">
                            <span class="material-symbols-outlined text-[14px] icon-filled">check_circle</span>
                            صحيح
                        </span>
                    @endif
                </div>

                <button
                    type="submit"
                    @disabled($this->componentSum !== 20)
                    class="btn-gradient text-on-primary px-5 py-2.5 rounded-lg text-sm font-semibold
                           hover:opacity-90 transition-opacity
                           disabled:opacity-40 disabled:cursor-not-allowed">
                    حفظ الإعدادات
                </button>

            </div>

        </form>
    </div>

    {{-- Visual breakdown --}}
    <div class="bg-surface-container-low rounded-xl p-5 shadow-ambient">
        <h3 class="text-sm font-bold text-on-surface-variant uppercase tracking-wider mb-4">توزيع النقاط</h3>
        <div class="space-y-3">
            @php
                $components = [
                    ['label' => 'الامتحان',        'value' => $examMax],
                    ['label' => 'الأعمال الشخصية', 'value' => $personalWorkMax],
                    ['label' => 'الحضور',          'value' => $attendanceMax],
                    ['label' => 'المشاركة',         'value' => $participationMax],
                ];
            @endphp
            @foreach ($components as $component)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-on-surface w-36 shrink-0">{{ $component['label'] }}</span>
                    <div class="flex-1 bg-surface-container-highest h-2 rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-300"
                             style="width: {{ $component['value'] > 0 ? round(($component['value'] / 20) * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-primary w-8 text-end">{{ $component['value'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
