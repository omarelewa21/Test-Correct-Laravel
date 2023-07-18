<x-layouts.base>
    <livewire:co-learning.header testName="{{ $testName ?? '' }}"/>

    <main class="flex flex-1 m-foot-head">
        {{ $slot  }}
    </main>

    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');
        </script>
    @endpush
</x-layouts.base>