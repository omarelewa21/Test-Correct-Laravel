<x-partials.question-container :number="$number" :question="$question">

    <div class="w-full space-y-3 matching-question">
        <div wire:ignore>
            {!!   $question->converted_question_html !!}
        </div>
        <div>
            <span>{!! __('test_take.instruction_matching_question') !!}</span>
        </div>
        @if($question->subtype == 'Classify')
            <div id="matching-container{{$question->getKey()}}" class="flex flex-col classify" wire:sortable-group="updateOrder">
                <div class="classified flex flex-1 flex-shrink-0 align-baseline flex-grow-0 flex-wrap flex-lg-row flex-md-column">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group" wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if($option->correct_answer_id !== null)
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}"
                                                     class="{{ empty($option->answer)  || $option->answer == ' ' ? 'hidden' : '' }}"
                                                     >

                                            {{ html_entity_decode($option->answer) }}
                                        </x-drag-item>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="classified flex flex-1 flex-shrink-0 align-baseline flex-grow-0 flex-wrap flex-lg-row flex-md-column">
                    @foreach ($groupItemOrder as $groupId => $items)
                        <x-dropzone id="dropzone{{$question->getKey()}}-{{$groupId}}"
                                    type="classify"
                                    title="{!! html_entity_decode($itemAnswerValues[$groupId]) !!}"
                                    wire:key="group-{{ $groupId }}"
                                    wire:sortable.item="{{ $groupId }}"
                        >
                            <div id="inner-dropzone{{$question->getKey()}}-{{$groupId}}"
                                 class="flex flex-col w-full dropzone-height"
                                 wire:sortable-group.item-group="{{ $groupId }}"
                                 selid="drag-block-input"
                            >
                                @foreach($items as $item)
                                    <x-drag-item id="drag_item{{$question->getKey()}}-{{$item['value']}}"
                                                 wire:key="option-{{ $item['value'] }}" sortableHandle="false"
                                                 wire:sortable-group.item="{{ $item['value'] }}"
                                                 selid="drag-block">
                                        {{ $itemAnswerValues[$item['value']] }}
                                    </x-drag-item>
                                @endforeach
                            </div>
                        </x-dropzone>
                    @endforeach
                </div>
            </div>
        @endif
        @if($question->subtype == 'Matching')
            <div id="matching-container{{$question->getKey()}}" class="flex flex-col space-y-1 matching" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group" wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}">
                                            {{ html_entity_decode($option->answer) }}
                                        </x-drag-item>
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
                                                 bg-primary-light font-size-18 bold base leading-5 select-none">
                                                {{ html_entity_decode($group->answer) }}
                                    </span>
                                </div>
                                <div class="flex-1 matching-dropzone">
                                    <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div class="flex w-full dropzone-height" wire:sortable-group.item-group="{{ $group->id }}">
                                            @foreach($shuffledAnswers as $option)
                                                @if(  $option->correct_answer_id !== null )
                                                    @if($answerStruct[$option->id] == $group->id)
                                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                                     wire:sortable-group.item="{{ $option->id }}">
                                                            {{ html_entity_decode($option->answer) }}
                                                        </x-drag-item>
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

    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
