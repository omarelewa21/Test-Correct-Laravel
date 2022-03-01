<div x-data="{expand: true}" class="flex flex-col px-6 py-1.5" style="max-width: 300px">
    <div class="flex space-x-2 items-center py-1.5 cursor-pointer"
         :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'"
         @click="expand = !expand"
    >
        <x-icon.chevron/>

        <span class="flex-1 truncate text-lg bold ">{{ $question->getQuestionHtml() }}</span>

        <div class="flex space-x-2.5 text-sysbase">
            <x-icon.locked/>
            <x-icon.options/>
        </div>
    </div>
    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200 group-question-questions"
         :style="expand ? 'max-height: ' + $el.scrollHeight + 'px' : ''"
    >
        {{ $slot }}
    </div>
</div>