<x-menu.context-menu.base context="question-card">
    <span></span>
    @if($this->isInCms())
    <x-menu.context-menu.button x-on:click="addQuestionToTest($el, uuid, contextData.showQuestionBankAddConfirmation )" x-show="!Alpine.store('questionBank').inGroup">
        <x-slot name="icon"><x-icon.plus/></x-slot>
        <x-slot name="text">{{ __('cms.Toevoegen') }}</x-slot>
    </x-menu.context-menu.button>
    @endif

    <x-menu.context-menu.button x-on:click="$wire.emit('openModal', 'teacher.question-detail-modal', {questionUuid: uuid, inTest: contextData.inTest})">
        <x-slot name="icon"><x-icon.settings/></x-slot>
        <x-slot name="text">{{ __('cms.Information') }}</x-slot>
    </x-menu.context-menu.button>

    <x-menu.context-menu.button x-on:click="$wire.emit('openModal', 'teacher.question-cms-preview-modal', {uuid: uuid, inTest: contextData.inTest})">
        <x-slot name="icon"><x-icon.preview/></x-slot>
        <x-slot name="text">{{ __('cms.voorbeeld') }}</x-slot>
    </x-menu.context-menu.button>
</x-menu.context-menu.base>