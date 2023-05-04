<x-layouts.base>

    <main id="assessment" class="flex flex-1 items-stretch isolate" style="margin-top: var(--header-height)">
        {{ $slot  }}
    </main>

    <x-notification/>
    @livewire('livewire-ui-modal')
</x-layouts.base>