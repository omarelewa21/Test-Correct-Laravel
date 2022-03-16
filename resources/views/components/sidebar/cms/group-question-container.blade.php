<div x-data="{expand: true}" class="flex flex-col px-6 py-1.5" style="max-width: 300px">
    <div class="flex space-x-2 items-center py-1.5 cursor-pointer"
         :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'"
         @click="expand = !expand; setTimeout(() => {handleVerticalScroll($refs.container1)}, 210); "
    >
        <x-icon.chevron/>

        <span class="flex-1 truncate text-lg bold"
              :class="($root.querySelectorAll('.question-button.active').length > 0 && !expand) ? 'primary' : ''"
        >{{ $question->getQuestionHtml() }}</span>

        <div class="flex space-x-2.5 text-sysbase">
            <x-icon.locked/>
            <x-icon.options/>
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