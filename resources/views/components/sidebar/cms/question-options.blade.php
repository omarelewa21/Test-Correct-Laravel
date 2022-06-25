<button class="px-2 flex rounded-md hover:text-primary transition relative"
        x-data="{options:false, topOffset: $el.getBoundingClientRect().top, updatetime: new Date() }"
        x-init="$watch('options', value => topOffset = $root.getBoundingClientRect().y)"
        @click.stop="options = true"
        @keydown.escape.stop="options = false"
        x-cloak
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
             title="{{ __('cms.Verwijderen') }}"
             @if($subQuestion)
                 wire:click="deleteSubQuestion('{{ $question->groupQuestionQuestionUuid }}', '{{ $testQuestion->uuid }}')"
            @else
                 wire:click="deleteQuestion('{{ $testQuestion->uuid }}')"
            @endif
                @click.stop="options = false; $el.classList.add('hidden')"
        >
            <x-icon.trash/>
            <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
        </div>
        <div class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
             title="{{ __('cms.Wijzigen') }}"
             @click.stop="options = false;$wire.emitTo('teacher.questions.open-short','showQuestion',
                {
                    'testQuestionUuid':'{{ $testQuestion ? $testQuestion->uuid : null }}',
                    'questionUuid': '{{ $question->uuid }}',
                    'isSubQuestion': {{ $subQuestion ? 1 : 0 }},
                    'shouldSave': true
                })"
        >
            <x-icon.edit/>
            <span class="text-base bold inherit">{{ __('cms.Wijzigen') }}</span>
        </div>
    </div>
</button>