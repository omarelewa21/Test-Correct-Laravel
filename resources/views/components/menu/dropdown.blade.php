@props(['name'])

<div x-description="Flyout menu, show/hide based on flyout menu state." x-show="{{ $name }}"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-1"
     class="absolute z-10 inset-x-0 transform border-b border-system-secondary"
     style="display: none;">
    <div class="flex px-28 py-2 space-x-6">
        {{ $slot }}
    </div>
</div>