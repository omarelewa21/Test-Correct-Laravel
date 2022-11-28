<x-content-section>
    <x-slot name="title">
        @if($this->obj instanceof \tcCore\Http\Livewire\Teacher\Questions\CmsInfoScreen)
            {{ __('cms.Informatietekst') }}
        @else
            {{ __('cms.Vraagstelling') }}
        @endif
    </x-slot>

    @yield('question-cms-question')
</x-content-section>