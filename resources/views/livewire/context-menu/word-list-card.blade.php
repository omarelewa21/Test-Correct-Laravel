<x-menu.context-menu.base context="word-list-card" class="w-60">

    <x-menu.context-menu.button x-on:click="alert('Howdy list')">
        <x-slot name="icon">
            <x-icon.grade />
        </x-slot>
        <x-slot name="text">Clickme</x-slot>
    </x-menu.context-menu.button>

</x-menu.context-menu.base>