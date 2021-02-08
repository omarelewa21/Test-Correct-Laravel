<x-partials.question-container :number="$number" :question="$question">

    <div class="w-full space-y-3">
        <div>
            <span>{!! __('test_take.instruction_matching_question') !!}</span>
        </div>
        <div>
            {!!   $question->getQuestionHtml() !!}
        </div>
        @if($question->subtype == 'Classify')
            <div class="flex flex-col" wire:sortable="updateGroupOrder" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div wire:sortable-group.item-group="startGroep">
                            @foreach($question->matchingQuestionAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                 wire:sortable-group.item="{{ $option->id }}">
                                        {{ $option->answer }}
                                    </x-drag-item>
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="flex space-x-5">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div class="min-h-full flex flex-col" wire:sortable-group.item-group="{{ $group->id }}">

                                </div>
                            </x-dropzone>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        @if($question->subtype == 'Matching')
            <div class="flex flex-col" wire:sortable="updateGroupOrder" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" startGroup="true">
                        <div wire:sortable-group.item-group="startGroep">
                            @foreach($question->matchingQuestionAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                 wire:sortable-group.item="{{ $option->id }}">
                                        {{ $option->answer }}
                                    </x-drag-item>
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
                                                {{ $group->answer }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div class="flex" wire:sortable-group.item-group="{{ $group->id }}"></div>
                                    </x-dropzone>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
