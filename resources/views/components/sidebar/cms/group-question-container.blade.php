<div x-data="{expand: true}"
     selid="group-question-{{$testQuestion->question->name}}"
     style="max-width: 300px"
     wire:key="group-{{ $testQuestion->uuid }}"
     wire:sortable.item="{{ $question->uuid }}"
     wire:sortable-group="updateGroupItemsOrder"
     title="{{ __('cms.Open vraaggroep') }}"
     uuid="{{ $question->uuid }}"
     @class([
         "drag-item flex flex-col py-1.5 draggable-group group-question-button",
         "group-active"   => $this->testQuestionId === $testQuestion->uuid,
         "group-error"    => isset($error['name']),
         "relation-error" => isset($error['name']) && $error['name'] === 'sub_questions_scores_relation'
     ])
>
    <div class="flex space-x-2 py-1.5 pl-6 pr-4 cursor-pointer group-question-title-container hover:bg-primary/5 hover:text-primary"
    >
        <span title="{{ __('cms.inklappen/uitklappen') }}" :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'">
            <x-icon.chevron class="mt-2 text-sysbase hover:text-primary z-1"
                            x-on:click.stop="expand = !expand; setTimeout(() => {handleVerticalScroll($refs.home);}, 210);"
            />
        </span>
        <span class="flex flex-1 flex-col truncate text-lg bold"
              :class="($root.querySelectorAll('.question-button.active').length > 0 && !expand) ? 'primary' : ''"
              title="{!! $question->name !!}"
              x-on:click.stop="openQuestion({
                                    testQuestionUuid: @js($testQuestion ? $testQuestion->uuid : null),
                                    questionUuid: @js( $question->uuid ),
                                    isSubQuestion: false,
                                    shouldSave: true
                                });"
        >
            <span class="truncate">{!! $question->name !!}</span>
            <div class="flex items-center justify-between">
                <span class="note text-sm regular">{{ trans_choice('cms.vraag', $question->subQuestions->count()) }}</span>
                @if($question->attachmentCount)
                    <span class="flex items-center note text-sm regular pr-2"><x-icon.attachment class="mr-1" /> {{ $question->attachmentCount }}</span>
                @endif
            </div>
        </span>

        <div class="flex items-start space-x-2.5 mt-2 text-sysbase">
            @isset($error['message'])
                <div class="flex h-full rounded-md" title="{{ $error['message'] }}">
                    <x-icon.exclamation class="all-red" />
                </div>
            @endisset
            <div class="flex h-full rounded-md hover:text-primary reorder"
                 title="{{ __('sidebar.reorder') }}"
                 wire:sortable.handle
                 wire:key="group-handle-{{ $testQuestion->uuid }}"
            >
                <x-icon.reorder />
            </div>
            <div class="flex">
                <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="false"
                                                :groupQuestion="true" />
            </div>
        </div>
    </div>
    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200 group-question-questions"
         :style="{maxHeight: expand ? $el.scrollHeight+20+'px' : '' }"
         wire:sortable-group.item-group="{{ $question->uuid }}"
         x-on:fix-expand-height="$nextTick(() => {expand = !expand; expand = !expand})"
    >
        {{ $slot }}

        <div class="group-add-new relative flex space-x-2.5 py-2 px-6 hover:text-primary cursor-pointer items-center"
             x-on:click="addQuestionToGroup('{{ $testQuestion->uuid }}')"
             wire:click="$set('groupId', '{{ $testQuestion->uuid }}')"
             selid="add-question-to-group-{{$testQuestion->question->name}}-btn"
        >
            <x-icon.plus-in-circle />
            <span class="flex bold">{{ __('cms.Vraag toevoegen')}}</span>
        </div>
    </div>
</div>