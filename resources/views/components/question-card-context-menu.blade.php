<div id="question-card-context-menu"
     class="absolute w-50 bg-white py-2 main-shadow rounded-10 z-1"
     x-data="questionCardContextMenu()"
     x-show="menuOpen"
     x-transition:enter="transition ease-out origin-top-right duration-200"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition origin-top-right ease-in duration-100"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     @question-card-context-menu-show.window="handleIncomingEvent($event.detail); "
     @question-card-context-menu-close.window="closeMenu(); "
     @click.outside="closeMenu()"
>
    <span></span>
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="addQuestionToTest($el, questionUuid)"
    >
        <span class="w-5 flex justify-center"><x-icon.plus-2/></span>
        <span class="bold">{{ __('cms.Toevoegen') }}</span>
    </button>
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$wire.openDetail(questionUuid, inTest)"
    >
        <span class="w-5 flex justify-center"><x-icon.settings/></span>
        <span class="bold">{{ __('cms.Instellingen') }}</span>
    </button>
    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$wire.openPreview(questionUuid, inTest)"
    >
        <span class="w-5 flex justify-center"><x-icon.preview/></span>
        <span class="bold">{{ __('cms.voorbeeld') }}</span>
    </button>
</div>