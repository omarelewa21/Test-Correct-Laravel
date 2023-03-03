<x-layouts.base>

    <main id="assessment" class="flex flex-1 items-stretch">
        {{ $slot  }}
    </main>

    <x-notification/>
    @livewire('livewire-ui-modal')
</x-layouts.base>