@if(session('success') || isset($message))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="flex items-start gap-3 px-4 py-3 bg-primary-fixed/30 border border-primary-fixed rounded-lg">
        <span class="material-symbols-outlined text-[20px] mt-0.5 flex-shrink-0 text-primary icon-filled"
             >check_circle</span>
        <p class="text-sm text-on-primary-fixed font-medium">{{ session('success') ?? $message }}</p>
        <button @click="show = false" class="mr-auto p-0.5 rounded hover:bg-black/10 transition-colors flex-shrink-0">
            <span class="material-symbols-outlined text-[16px] text-on-primary-fixed">close</span>
        </button>
    </div>
@endif
