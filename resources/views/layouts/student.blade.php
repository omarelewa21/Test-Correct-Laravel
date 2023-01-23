<x-layouts.base>
    <livewire:student.header/>

    <main class="flex flex-col items-center">
        {{ $slot  }}
    </main>

    <x-notification/>

    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');
        </script>
    @endpush
    @livewire('livewire-ui-modal')
</x-layouts.base>