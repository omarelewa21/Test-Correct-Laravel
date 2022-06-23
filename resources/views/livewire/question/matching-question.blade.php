<x-partials.question-container :number="$number" :question="$question">

    <div class="w-full space-y-3 matching-question">
        <div questionHtml wire:ignore>
            {!!   $question->converted_question_html !!}
        </div>
        <div>
            <span>{!! __('test_take.instruction_matching_question') !!}</span>
        </div>
        @if($question->subtype == 'Classify')
            <div id="matching-container{{$question->getKey()}}" class="flex flex-col classify"
                 wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone id="startGroep-{{$question->getKey()}}" wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group"
                             wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if($option->correct_answer_id !== null)
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item id="drag_item{{$question->getKey()}}-{{$option->id}}"
                                                     wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}" selid="drag-block"
                                                     class="{{ empty($option->answer) || $option->answer == ' ' ? 'hidden' : '' }}"
                                                     >
                                            {{ html_entity_decode($option->answer) }}
                                        </x-drag-item>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="flex space-x-5 classified">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <x-dropzone id="dropzone{{$question->getKey()}}-{{$group->id}}" type="classify"
                                        title="{!! html_entity_decode($group->answer) !!}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div id="inner-dropzone{{$question->getKey()}}-{{$group->id}}"
                                     class="flex flex-col w-full dropzone-height"
                                     wire:sortable-group.item-group="{{ $group->id }}" selid="drag-block-input">
                                    @foreach($shuffledAnswers as $option)
                                        @if(  $option->correct_answer_id !== null )
                                            @if($answerStruct[$option->id] == $group->id)
                                                <x-drag-item id="drag_item{{$question->getKey()}}-{{$option->id}}"
                                                             wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                             wire:sortable-group.item="{{ $option->id }}" selid="drag-block">
                                                    {{ html_entity_decode($option->answer) }}
                                                </x-drag-item>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </x-dropzone>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        @if($question->subtype == 'Matching')
            <div id="matching-container{{$question->getKey()}}" class="flex flex-col space-y-1 matching"
                 wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group"
                             wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item id="drag_item{{$question->getKey()}}-{{$option->id}}"
                                                     wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}" selid="drag-block">
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
                            <div id="dropdiv{{$question->getKey()}}-{{$group->id}}" class="flex space-x-2">
                                <div class="w-1/3">
                                    <span class="flex w-full py-2.5 px-4 border-2 border-blue-grey rounded-10
                                                 bg-primary-light font-size-18 bold base leading-5 ">
                                                {{ html_entity_decode($group->answer) }}
                                    </span>
                                </div>
                                <div class="flex-1 matching-dropzone">
                                    <x-dropzone id="dropzone{{$question->getKey()}}-{{$group->id}}" type="matching"
                                                wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div id="inner-dropzone{{$question->getKey()}}-{{$group->id}}"
                                             class="flex w-full dropzone-height"
                                             wire:sortable-group.item-group="{{ $group->id }}"
                                        selid="drag-block-input">
                                            @foreach($shuffledAnswers as $option)
                                                @if(  $option->correct_answer_id !== null )
                                                    @if($answerStruct[$option->id] == $group->id)
                                                        <x-drag-item
                                                                id="drag_item{{$question->getKey()}}-{{$option->id}}"
                                                                wire:key="option-{{ $option->id }}"
                                                                sortableHandle="false"
                                                                wire:sortable-group.item="{{ $option->id }}" selid="drag-block">
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
    @if(!$this->closed)
    @push('scripts')
        <script>
            var draggableElementWidth;
            setTimeout(function () {
                document.getElementById('matching-container{{$question->getKey()}}').livewire_sortable.on('mirror:attached', (evt) => {
                    draggableElementWidth = evt.data.mirror.parentElement.offsetWidth+'px';
                });

                document.getElementById('matching-container{{$question->getKey()}}').livewire_sortable.on('mirror:move', (evt) => {
                    evt.data.mirror.style.width = draggableElementWidth;
                });
            }, 100);
        </script>
    @endpush
    @endif
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
