@props([
    'striped' => false,
])

<div {{ $attributes->class(['w-full overflow-x-auto rounded-xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(25,28,29,0.05)]']) }}>
    <table class="w-full text-sm text-on-surface">
        {{ $slot }}
    </table>
</div>
