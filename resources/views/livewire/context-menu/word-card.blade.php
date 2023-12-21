<x-menu.context-menu.base context="word-card" class="w-60">

    <x-menu.context-menu.button x-on:click="alert('Howdy word')">
        <x-slot name="icon">
            <x-icon.grade />
        </x-slot>
        <x-slot name="text">Clickme</x-slot>
    </x-menu.context-menu.button>

</x-menu.context-menu.base>