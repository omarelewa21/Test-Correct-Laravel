<x-layouts.base>
    <livewire:student.header/>

    <main class="flex flex-col items-center">
        {{ $slot  }}
    </main>

    <x-notification/>
</x-layouts.base>