<div wire:loading.class.remove="hidden"  class="animate-borderpulse border-6 rounded-10 hidden" style="min-height: 180px; height: 180px; animation-delay: calc({{ $delay }} * 200ms)">
    {{ $slot }}
</div>