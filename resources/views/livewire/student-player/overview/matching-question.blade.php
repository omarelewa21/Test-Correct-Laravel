<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full space-y-3 matching-question">
        <div class="questionhtml">
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')
            <div class="flex flex-col classify">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none">
                            @foreach($shuffledAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                              wire:sortable-group.item="{{ $option->id }}"
                                                              style="{{ empty($option->answer) || $option->answer == ' ' ? 'display:none !important' : '' }}"
                                        >
                                            {{ html_entity_decode($option->answer) }}
                                        </x-drag-item-disabled>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="classified gap-5 flex flex-1 flex-shrink-0 align-baseline flex-grow-0 flex-wrap flex-lg-row flex-md-column">
                    @foreach ($groupItemOrder as $groupId => $items)
                        <x-dropzone type="classify"
                                    title="{!! html_entity_decode($itemAnswerValues[$groupId]) !!}"
                                    wire:key="group-{{ $groupId }}"
                                    wire:sortable.item="{{ $groupId }}"
                        >
                            <div class="flex flex-col w-full dropzone-height" selid="drag-block-input">
                                @foreach($items as $item)
                                    <x-drag-item-disabled wire:key="option-{{ $item['value'] }}"
                                                          sortableHandle="false"
                                                          wire:sortable-group.item="{{ $item['value'] }}">
                                        {{ $itemAnswerValues[$item['value']] }}
                                    </x-drag-item-disabled>
                                @endforeach
                            </div>
                        </x-dropzone>
                    @endforeach
                </div>
            </div>
        @endif
        @if($question->subtype == 'Matching')
            <div class="flex flex-col space-y-1 matching">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none">
                            @foreach($shuffledAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                              wire:sortable-group.item="{{ $option->id }}">
                                            {{ html_entity_decode($option->answer) }}
                                        </x-drag-item-disabled>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="flex flex-col space-y-3">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <div class="flex space-x-2">
                                <div class="w-1/3">
                                        <span class="flex w-full py-2 px-4 border-2 border-blue-grey rounded-10
                                                     bg-primary-light font-size-18 bold base leading-5">
                                                    {{ html_entity_decode($group->answer) }}
                                        </span>
                                </div>
                                <div class="flex-1 matching-dropzone">
                                    <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div class="flex w-full dropzone-height" selid="drag-block-input">
                                            @foreach($shuffledAnswers as $option)
                                                @if(  $option->correct_answer_id !== null )
                                                    @if($answerStruct[$option->id] == $group->id)
                                                        <x-drag-item-disabled wire:key="option-{{ $option->id }}"
                                                                              sortableHandle="false"
                                                                              wire:sortable-group.item="{{ $option->id }}">
                                                            {{ html_entity_decode($option->answer) }}
                                                        </x-drag-item-disabled>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </x-dropzone>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</x-partials.overview-question-container>