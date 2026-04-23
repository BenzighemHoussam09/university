@if($errors->any())
    <div class="px-4 py-3 bg-error-container/40 border border-error-container rounded-lg">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[18px] text-error icon-filled">error</span>
            <p class="text-sm font-semibold text-on-error-container">يرجى تصحيح الأخطاء التالية:</p>
        </div>
        <ul class="space-y-1 pr-6">
            @foreach($errors->all() as $error)
                <li class="text-xs text-on-error-container list-disc">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
