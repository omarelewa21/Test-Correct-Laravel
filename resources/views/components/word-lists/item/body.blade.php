<div class="flex flex-col flex-1">
    <div x-show="expanded" x-collapse class="flex flex-col gap-4">
        {{ $slot }}
    </div>
</div>