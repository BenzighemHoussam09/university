@if(session('error') || isset($message))
    <div class="flex items-start gap-3 px-4 py-3 bg-error-container/40 border border-error-container rounded-lg">
        <span class="material-symbols-outlined text-[20px] mt-0.5 flex-shrink-0 text-error icon-filled"
             >cancel</span>
        <p class="text-sm text-on-error-container font-medium">{{ session('error') ?? $message }}</p>
    </div>
@endif
