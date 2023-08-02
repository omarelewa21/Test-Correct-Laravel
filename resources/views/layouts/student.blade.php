<x-layouts.base>
    <livewire:student.header/>

    <main class="flex flex-col items-center">
        {{ $slot  }}
    </main>

    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');
        </script>
    @endpush
</x-layouts.base>