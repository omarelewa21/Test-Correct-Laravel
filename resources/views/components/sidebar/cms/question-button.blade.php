<div class="drag-item question-button flex items-center cursor-pointer bold py-2 bg-white transition-colors hover:text-primary pl-6 pr-4 {{ $active ? 'question-active' : '' }}"
     @click="$store.cms.processing = true;
             $dispatch('store-current-question');
             $wire.emitTo('teacher.questions.open-short','showQuestion',
             {
                'testQuestionUuid':'{{ $testQuestion ? $testQuestion->uuid : null }}',
                'questionUuid': '{{ $question->uuid }}',
                'isSubQuestion': {{ $subQuestion ? 1 : 0 }},
                'shouldSave': true
                })
             $store.cms.scrollPos = document.querySelector('.drawer').scrollTop;
                "
     style="max-width: 300px"
     @if($subQuestion)
        wire:sortable-group.item="{{ $question->uuid }}"
     @else
        wire:sortable.item="{{ $question->uuid }}"
     @endif
>
    <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5
              {{ $active ? 'text-white bg-primary border-primary ' : 'bg-transparent border-current' }}"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number">{{ $loop }}</span>
        </span>
        <div class="flex mt-.5 flex-1">
            <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="truncate"
                      style="{{ $double ? 'max-width: 145px; width: 145px' : 'max-width: 160px; width: 160px' }}"
                      title="{{ $question->title }}">{{ $question->title }}</span>
                <div class="flex note text-sm regular justify-between">
                    <span>{{ $question->typeName }}</span>
                    <div class="flex items-center space-x-2">
                        <span class="flex">{{ $question->score }}pt</span>
                        @if($subQuestion === false)
                            <div class="flex items-center space-x-1 @if($question->attachmentCount === 0) invisible @endif">
                                <x-icon.attachment class="flex"/>
                                <span class="flex">{{ $question->attachmentCount }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-start space-x-2.5 mt-1 text-sysbase">
                @if($double)
                    <div class="flex h-full rounded-md" title="{{ __('cms.duplicate_question_in_test') }}">
                        <x-icon.exclamation class="all-red"/>
                    </div>
                @endif
                <div class="flex h-full rounded-md hover:text-primary" @if($subQuestion) wire:sortable-group.handle @else wire:sortable.handle @endif>
                    <x-icon.reorder/>
                </div>
                <div class="flex">
                    <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="$subQuestion"/>
                </div>
            </div>
        </div>
    </div>
</div>
