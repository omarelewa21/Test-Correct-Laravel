<x-context-menu-base context="question-card">
    <span></span>
    @if($this->isInCms())
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="addQuestionToTest($el, uuid)"
    >
        <span class="w-5 flex justify-center"><x-icon.plus-2/></span>
        <span class="bold">{{ __('cms.Toevoegen') }}</span>
    </button>
    @endif
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$wire.emit('openModal', 'teacher.question-detail-modal', {questionUuid: uuid, inTest})"
    >
        <span class="w-5 flex justify-center"><x-icon.settings/></span>
        <span class="bold">{{ __('cms.Instellingen') }}</span>
    </button>
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$wire.emit('openModal', 'teacher.question-cms-preview-modal', {uuid: uuid, inTest})"
    >
        <span class="w-5 flex justify-center"><x-icon.preview/></span>
        <span class="bold">{{ __('cms.voorbeeld') }}</span>
    </button>
</x-context-menu-base>