<x-layouts.base>
    <livewire:co-learning.header testName="{{ $testName ?? '' }}"/>

    <main class="flex flex-1 items-stretch mx-8 xl:mx-28 m-foot-head">
        {{ $slot  }}
    </main>



    <x-notification/>
    @livewire('livewire-ui-modal')
    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');
        </script>
    @endpush
</x-layouts.base>