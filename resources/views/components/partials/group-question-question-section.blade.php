<x-content-section>
    <x-slot name="title">
        @if($this->isInfoscreenQuestion())
            {{ __('cms.Informatietekst') }}
        @else
            {{ __('cms.Vraagstelling') }}
        @endif
    </x-slot>

    @yield('question-cms-question')
</x-content-section>