<x-partials.modal.preview>

{{--    @hasSection('content')--}}
        @if(false)

        @yield('content')

    @else
        <div class="w-full h-full flex justify-center items-center">
            Preview not available
        </div>
    @endif

    <x-slot name="icon">
        <x-dynamic-component :component="$iconComponentName"/>
    </x-slot>
</x-partials.modal.preview>