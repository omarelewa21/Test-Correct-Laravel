<x-layouts.base>
    <div id="cms-container" class="min-h-screen">
        @if(Auth::user()->schoolLocation->allow_cms_drawer && Auth::user()->schoolLocation->allow_new_drawing_question)
            <livewire:drawer.cms/>
        @endif
        {{ $slot }}
    </div>
</x-layouts.base>