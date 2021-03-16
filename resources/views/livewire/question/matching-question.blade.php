<x-partials.question-container :number="$number" :question="$question">

    <div class="w-full space-y-3 matching-question">
        <div>
            {!!   $question->getQuestionHtml() !!}
        </div>
        <div>
            <span>{!! __('test_take.instruction_matching_question') !!}</span>
        </div>
        @if($question->subtype == 'Classify')
            <div class="flex flex-col classify" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group" wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if($option->correct_answer_id !== null)
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}">
                                            {{ $option->answer }}
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
                            <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div class="flex flex-col w-full h-full" wire:sortable-group.item-group="{{ $group->id }}">
                                    @foreach($shuffledAnswers as $option)
                                        @if(  $option->correct_answer_id !== null )
                                            @if($answerStruct[$option->id] == $group->id)
                                                <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                             wire:sortable-group.item="{{ $option->id }}">
                                                    {{ $option->answer }}
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
            <div class="flex flex-col space-y-1" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div class="h-full space-x-1 focus:outline-none start-group" wire:sortable-group.item-group="startGroep">
                            @foreach($shuffledAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    @if($answerStruct[$option->id] === '')
                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                     wire:sortable-group.item="{{ $option->id }}">
                                            {{ $option->answer }}
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
                                                {{ $group->answer }}
                                    </span>
                                </div>
                                <div class="flex-1 matching-dropzone">
                                    <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div class="flex w-full h-full" wire:sortable-group.item-group="{{ $group->id }}">
                                            @foreach($shuffledAnswers as $option)
                                                @if(  $option->correct_answer_id !== null )
                                                    @if($answerStruct[$option->id] == $group->id)
                                                        <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                                     wire:sortable-group.item="{{ $option->id }}">
                                                            {{ $option->answer }}
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
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
