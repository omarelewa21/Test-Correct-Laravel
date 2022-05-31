<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full space-y-3 matching-question">
        <div>
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')
            <table class="prevent-pagebreak-table prevent-pagebreak">
                <tr>
                    <td>
                        <div class="flex flex-col classify " style="margin-top: 40px;">
                            <div class="flex space-x-5 classified">
                                @foreach ($question->matchingQuestionAnswers as $group)
                                    @if(  $group->correct_answer_id === null )
                                        <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                                    wire:sortable.item="{{ $group->id }}">
                                            <div class="flex flex-col w-full dropzone-height" selid="drag-block-input" >
                                                @foreach($question->matchingQuestionAnswers as $option)
                                                    @if(  $option->correct_answer_id !== null )
                                                        @if($option->correct_answer_id == $group->id)
                                                            <x-drag-item-disabled wire:key="option-{{ $option->id }}"
                                                                                  sortableHandle="false"
                                                                                  wire:sortable-group.item="{{ $option->id }}">
                                                                {{ $option->answer }}
                                                            </x-drag-item-disabled>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </x-dropzone>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

        @endif
        @if($question->subtype == 'Matching')
            <div class="flex flex-col space-y-1 matching prevent-pagebreak">
                <div class="flex flex-col space-y-3">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <div class="flex space-x-2" style="position: relative">
                                <div class="w-1/3 label-dropzone" style="width:33%;display: inline-flex;position: relative;top:2px;">
                                        <span class="flex w-full py-2 px-4 border-2 border-blue-grey rounded-10
                                                     bg-primary-light font-size-18 bold base leading-5">
                                                    {{ $group->answer }}
                                        </span>
                                </div>
                                <div class="flex-1 matching-dropzone" style="width:50%;display: inline-flex;position: relative;">
                                    <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                wire:sortable.item="{{ $group->id }}">
                                        <div class="flex w-full dropzone-height" selid="drag-block-input">
                                            @foreach($question->matchingQuestionAnswers as $option)
                                                @if(  $option->correct_answer_id !== null )
                                                    @if($option->correct_answer_id == $group->id)
                                                        <x-drag-item-disabled wire:key="option-{{ $option->id }}"
                                                                              sortableHandle="false"
                                                                              wire:sortable-group.item="{{ $option->id }}">
                                                            {{ $option->answer }}
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
</x-partials.answer-model-question-container>