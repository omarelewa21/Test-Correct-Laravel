<x-layouts.base>
    <livewire:student.header/>

    <main class="flex flex-col items-center">
        {{ $slot  }}
    </main>

    <x-notification/>

    @push('scripts')
        <script>
            Echo.connector.pusher.config.auth.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}'
        </script>
    @endpush
</x-layouts.base>