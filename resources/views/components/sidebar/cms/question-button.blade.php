<div class="relative drag-item question-button flex items-center cursor-pointer bold py-2 bg-white transition-colors hover:text-primary pl-6 pr-4 {{ $active ? 'question-active' : '' }}"
     x-on:click="openQuestion({
                testQuestionUuid: @js($testQuestion ? $testQuestion->uuid : null),
                questionUuid: @js( $question->uuid ),
                isSubQuestion: @js($subQuestion),
                shouldSave: true
     });
    "
     title="{{ __('cms.Open vraag') }}"
     style="max-width: 300px"
     data-order-number="{{ $loop }}"
     selid="question-list-entry"
     uuid="{{ $question->uuid }}"
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
                    <span class="truncate max-w-[100px]" selid="specified-type-name">{{ $question->typeName }}</span>
                    <div class="flex items-center space-x-2">
                        <span class="flex">{{ $question->score }}pt</span>
                        <div class="flex items-center space-x-1 @if($question->attachmentCount === 0) invisible @endif">
                            <x-icon.attachment class="flex"/>
                            <span class="flex">{{ $question->attachmentCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-start space-x-2.5 mt-1 text-sysbase">
                @if($double)
                    <div class="flex h-full rounded-md" title="{{ __('cms.duplicate_question_in_test') }}">
                        <x-icon.exclamation class="all-red"/>
                    </div>
                @endif
                <div class="flex h-full rounded-md hover:text-primary reorder" @if($subQuestion) wire:sortable-group.handle @else wire:sortable.handle @endif title="{{ __('sidebar.reorder') }}">
                    <x-icon.reorder/>
                </div>
                <div class="flex">
                    <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="$subQuestion"/>
                </div>
            </div>
        </div>
    </div>
</div>
