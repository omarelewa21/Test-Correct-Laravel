<div x-data="{expand: true}"
     class="drag-item flex flex-col py-1.5 draggable-group {{ ($this->testQuestionId == $testQuestion->uuid) ? 'group-active' : '' }}"
     style="max-width: 300px"
     wire:key="group-{{ $testQuestion->uuid }}"
     wire:sortable.item="{{ $question->uuid }}"
     title="{{ __('sidebar.group-question') }}"
>
    <div class="flex space-x-2 py-1.5 pl-6 pr-4 cursor-pointer group-question-title-container hover:bg-primary/5 hover:text-primary"
         :class="expand ? 'rotate-svg-270' : 'rotate-svg-90'"
    >
        <x-icon.chevron class="mt-2 text-sysbase hover:text-primary"
                        @click.stop="expand = !expand; setTimeout(() => {handleVerticalScroll($refs.container1);}, 210);"
        />
        <span class="flex flex-1 flex-col truncate text-lg bold"
              :class="($root.querySelectorAll('.question-button.active').length > 0 && !expand) ? 'primary' : ''"
              title="{{ $question->name }}"
              @click.stop="
                  $store.cms.processing = true;
                  $dispatch('store-current-question');
                  $wire.emitTo('teacher.questions.open-short','showQuestion',
                    {
                        'testQuestionUuid':'{{ $testQuestion ? $testQuestion->uuid : null }}',
                        'questionUuid': '{{ $question->uuid }}',
                        'isSubQuestion': false,
                        'shouldSave': true
                    })
                    "
        >
            <span class="truncate">{{ $question->name }}</span>
            <div class="flex items-center justify-between">
                <span class="note text-sm regular">{{ trans_choice('cms.vraag', $question->subQuestions->count()) }}</span>
                @if($question->attachmentCount)
                    <span class="flex items-center note text-sm regular pr-2"><x-icon.attachment class="mr-1"/> {{ $question->attachmentCount }}</span>
                @endif
            </div>
        </span>

        <div class="flex items-start space-x-2.5 mt-2 text-sysbase">
            @if($double)
                <div class="flex h-full rounded-md" title="{{ __('cms.duplicate_question_in_test') }}">
                    <x-icon.exclamation class="all-red"/>
                </div>
            @elseif($question->isCarouselQuestion())
                @if(!$question->hasEnoughSubQuestionsAsCarousel())
                    <div class="flex h-full rounded-md" title="{{ __('cms.carousel_not_enough_questions') }}">
                        <x-icon.exclamation class="all-red"/>
                    </div>
                @elseif(!$question->hasEqualScoresForSubQuestions())
                    <div class="flex h-full rounded-md" title="{{ __('cms.carousel_subquestions_scores_differ') }}">
                        <x-icon.exclamation class="all-red"/>
                    </div>
                @endif
            @endif
            <div class="flex h-full rounded-md hover:text-primary reorder" wire:sortable.handle>
                    <x-icon.reorder/>
            </div>
            <div class="flex">
                <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="false"/>
            </div>
        </div>
    </div>
    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200 group-question-questions"
         :style="expand ? 'max-height:' + $el.scrollHeight + 'px' : ''"
         wire:sortable-group.item-group="{{ $question->uuid }}"
    >
        {{ $slot }}

        <div class="group-add-new relative flex space-x-2.5 py-2 px-6 hover:text-primary cursor-pointer items-center"
             @click="addQuestionToGroup('{{ $testQuestion->uuid }}')"
             wire:click="$set('groupId', '{{ $testQuestion->uuid }}')"
             selid="add-question-to-group-{{$testQuestion->question->name}}-btn"
        >
            <x-icon.plus-in-circle/>
            <span class="flex bold">{{ __('cms.Vraag toevoegen')}}</span>
        </div>
    </div>
</div>