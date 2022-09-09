<x-menu.context-menu.base context="question-card">
    <span></span>
    @if($this->isInCms())
    <x-menu.context-menu.button x-on:click="addQuestionToTest($el, uuid)">
        <x-slot name="icon"><x-icon.plus-2/></x-slot>
        <x-slot name="text">{{ __('cms.Toevoegen') }}</x-slot>
    </x-menu.context-menu.button>
    @endif

    <x-menu.context-menu.button x-on:click="$wire.emit('openModal', 'teacher.question-detail-modal', {questionUuid: uuid, inTest})">
        <x-slot name="icon"><x-icon.settings/></x-slot>
        <x-slot name="text">{{ __('cms.Instellingen') }}</x-slot>
    </x-menu.context-menu.button>

    <x-menu.context-menu.button x-on:click="$wire.emit('openModal', 'teacher.question-cms-preview-modal', {uuid: uuid, inTest})">
        <x-slot name="icon"><x-icon.preview/></x-slot>
        <x-slot name="text">{{ __('cms.voorbeeld') }}</x-slot>
    </x-menu.context-menu.button>
</x-menu.context-menu.base>