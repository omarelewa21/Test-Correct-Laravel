<x-layouts.base>
    <div id="cms-container" class="min-h-screen" x-data="constructionDirector">
        @if(Auth::user()->schoolLocation->canUseCmsWithDrawer() && request()->query('withDrawer'))
            <livewire:drawer.cms/>
        @endif
        {{ $slot }}
    </div>
</x-layouts.base>