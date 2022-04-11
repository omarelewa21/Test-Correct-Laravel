<div x-data="{expand: true}" class="flex flex-col px-6 py-1.5" style="max-width: 300px">
    <div class="flex space-x-2 items-center py-1.5 cursor-pointer"
         :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'"
         @click="expand = !expand; setTimeout(() => {handleVerticalScroll($refs.container1)}, 210); "
    >
        <x-icon.chevron/>

        <span class="flex-1 truncate text-lg bold"
              :class="($root.querySelectorAll('.question-button.active').length > 0 && !expand) ? 'primary' : ''"
              title="{{ $question->title }}"
        >{{ $question->title }}</span>

        <div class="flex space-x-2.5 text-sysbase"
             x-data="{
             options:false,
             toggleOptions(e){
             e.stopPropagation();
             this.options=true;
             },
             hideOptions(e){
                e.stopImmediatePropagation();
                e.preventDefault();
                e.stopPropagation();
                this.options = false;
                return false;
             }
             }">
            <div class="py-3 flex items-center h-full rounded-md">
            <x-icon.locked />
        </div>
            <button class="py-3 px-1 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition relative"
                    @click.stop="toggleOptions($event)"
            >
            <x-icon.options/>
                <div x-cloak
                     x-show="options"
                     x-ref="optionscontainer"
                     class="absolute -right-5 top-10 bg-white text-sysbase py-2 main-shadow rounded-10 w-72 z-10"
                     @click.outside="hideOptions($event)"

                     x-transition:enter="transition ease-out origin-top-right duration-200"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition origin-top-right ease-in duration-100"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                >
               <div class="p-2  text-base max-w-[200px] truncate" title="{{ __('Verwijderen') }}">
              <x-icon.trash/> {{ __('Verwijderen') }}
        </div>
        <div
            class="p-2  text-base max-w-[200px] truncate"
            title="{{ __('Wijzigen') }}"
            wire:click="showQuestion('{{ $testQuestion ? $testQuestion->uuid : null }}', '{{ $question->uuid }}', false)"
            @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question->uuid }}' })"
        >
              <x-icon.edit/> {{ __('Wijzigen') }}
        </div>
      </div>
        </button>
      </div>
    </div>
    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200 group-question-questions"
         :style="expand ? 'max-height:' + $el.scrollHeight + 'px' : ''"
    >
        {{ $slot }}

        <div class="group-add-new relative flex space-x-2.5 py-2 hover:text-primary cursor-pointer items-center"
             @click="next($refs.container1);"
             wire:click="$set('groupId', '{{ $testQuestion->uuid }}')"
        >
            <x-icon.plus-in-circle/>
            <span class="flex bold">{{ __('cms.Vraag toevoegen')}}</span>
        </div>
    </div>
</div>