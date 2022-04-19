<div x-data="{expand: true}"
     class="flex flex-col py-1.5 pl-6 pr-4 {{ ($this->testQuestionId == $testQuestion->uuid) ? 'group-active' : '' }}"
     style="max-width: 300px">
    <div class="flex space-x-2 py-1.5 cursor-pointer group-question-title-container"
         :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'"
         @click="expand = !expand; setTimeout(() => {handleVerticalScroll($refs.container1)}, 210); "
    >
        <x-icon.chevron class="mt-2"/>
        <span class="flex flex-1 flex-col truncate text-lg bold"
              :class="($root.querySelectorAll('.question-button.active').length > 0 && !expand) ? 'primary' : ''"
              title="{{ $question->name }}"
        >
            <span>{{ $question->name }}</span>
            <span class="note text-sm regular">{{ trans_choice('cms.vraag', $question->subQuestions->count()) }}</span>
        </span>

        <div class="flex items-start space-x-2.5 mt-2 text-sysbase">
            <div class="flex h-full rounded-md">
                @if($question->closeable)
                    <x-icon.locked/>
                @else
                    <x-icon.unlocked class="note"/>
                @endif
            </div>
            <div class="flex">
                <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="false"/>
            </div>
        </div>
    </div>
    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200 group-question-questions"
         :style="expand ? 'max-height:' + $el.scrollHeight + 'px' : ''"
    >
        {{ $slot }}

        <div class="group-add-new relative flex space-x-2.5 py-2 hover:text-primary cursor-pointer items-center"
             @click="next($refs.container1);$dispatch('backdrop')"
             wire:click="$set('groupId', '{{ $testQuestion->uuid }}')"
        >
            <x-icon.plus-in-circle/>
            <span class="flex bold">{{ __('cms.Vraag toevoegen')}}</span>
        </div>
    </div>
</div>