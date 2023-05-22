<x-content-section>
    <x-slot name="title">
        @if($this->obj instanceof \tcCore\Http\Livewire\Teacher\Cms\Providers\InfoScreen)
            {{ __('cms.Informatietekst') }}
        @else
            {{ __('cms.Vraagstelling') }}
        @endif
    </x-slot>

    @yield('question-cms-question')
</x-content-section>