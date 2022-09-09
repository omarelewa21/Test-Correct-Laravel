<x-menu.context-menu.base context="test-take-card">

    <x-menu.context-menu.button wire:click="openTestTakeDetail">
        <x-slot name="icon"><x-icon.arrow/></x-slot>
        <x-slot name="text">{{ __('test-take.Open') }}</x-slot>
    </x-menu.context-menu.button>

    <x-menu.context-menu.button>
        <x-slot name="icon"><x-icon.pdf-file/></x-slot>
        <x-slot name="text">{{ __('test-take.Antwoord PDF') }}</x-slot>
    </x-menu.context-menu.button>

    <x-menu.context-menu.button wire:click="skipDiscussing">
        <x-slot name="icon"><x-icon.grading/></x-slot>
        <x-slot name="text">{{ __('test-take.Direct nakijken') }}</x-slot>
    </x-menu.context-menu.button>

    <x-menu.context-menu.button wire:click="archive">
        <x-slot name="icon"><x-icon.plus-2/></x-slot>
        <x-slot name="text">{{ __('test-take.Archiveren') }}</x-slot>
    </x-menu.context-menu.button>

</x-menu.context-menu.base>