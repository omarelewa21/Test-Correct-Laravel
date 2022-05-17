<x-layouts.base>
    <div id="cms-container" class="min-h-screen">
        @if(Auth::user()->schoolLocation->canUseCmsWithDrawer() && request()->query('withDrawer'))
            <livewire:drawer.cms/>
        @endif
        {{ $slot }}
    </div>
    <x-notification/>
</x-layouts.base>