<x-layouts.base>
    <div id="cms-container" class="min-h-screen">
        @if(Auth::user()->schoolLocation->canUseCmsWithDrawer())
            <livewire:drawer.cms/>
        @endif
        {{ $slot }}
    </div>
</x-layouts.base>