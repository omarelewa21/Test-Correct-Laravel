<button class="px-2 flex rounded-md hover:text-primary transition relative"
        x-data="{options:false, topOffset: $el.getBoundingClientRect().top, updatetime: new Date() }"
        x-init="$watch('options', value => topOffset = $root.getBoundingClientRect().y)"
        @click.stop="options = true"
        @keydown.escape.stop="options = false"
        x-cloak
        title="{{ __('sidebar.options') }}"
        selid="question-more-options-btn"
>
    <div x-show="options" @click.stop="options=false" class="fixed inset-0 cursor-default z-10"
         style="width: var(--sidebar-width)"></div>
    <x-icon.options/>
    <div x-cloak
         x-show="options"
         x-ref="optionscontainer"
         class="flex flex-col left-5 bg-white text-sysbase py-2 main-shadow rounded-10 w-72 z-10"
         :class="options ? 'fixed' : 'hidden' "
         @click.outside="options = false"
         :style="'top:' + (topOffset + 25) + 'px'"
         x-transition:enter="transition ease-out origin-top-right duration-200"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition origin-top-right ease-in duration-100"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
    >
        <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
             selid="delete-question-btn"
             title="{{ __('cms.Verwijderen') }}"
             @if($subQuestion)
                 wire:click="deleteSubQuestion('{{ $question->groupQuestionQuestionUuid }}', '{{ $testQuestion->uuid }}')"
            @else
                 wire:click="deleteQuestion('{{ $testQuestion->uuid }}')"
            @endif
                @click.stop="options = false; $el.classList.add('hidden');$store.cms.scrollPos = document.querySelector('.drawer').scrollTop"
        >
            <x-icon.trash/>
            <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
        </div>
        @if(!$groupQuestion)
            <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                 title="{{ __('cms.Kopie maken') }}"
                 selid="copy-question-btn"
                 @click.stop="options = false; $store.cms.scrollPos = document.querySelector('.drawer').scrollTop"
                 @if($subQuestion)
                 wire:click="duplicateQuestion('{{ $question->uuid }}', '{{ $testQuestion->uuid }}')"
                 @else
                 wire:click="duplicateQuestion('{{ $question->uuid }}')"
                 @endif
            >
                <x-icon.edit/>
                <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
            </div>
        @endif
    </div>
</button>