<x-layouts.base>
    <x-partials.header.student/>

    <main class="flex flex-col items-center">
        {{ $slot  }}
    </main>

    <x-notification/>
</x-layouts.base>