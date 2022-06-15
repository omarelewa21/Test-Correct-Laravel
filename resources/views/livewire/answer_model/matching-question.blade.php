<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full space-y-3 matching-question">
        <div>
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')

                        <div class="flex flex-col classify " style="margin-top: 40px;">
                            <div class="flex space-x-5 classified">
                                <table class="prevent-pagebreak-table question-no-break-classify-option no-border">
                                    <tr class="no-border">

                                @foreach ($question->matchingQuestionAnswers as $group)
                                    @if(  $group->correct_answer_id === null )
                                    <td class="no-border">
                                        <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                                    wire:sortable.item="{{ $group->id }}">
                                            <div class="flex flex-col w-full dropzone-height" selid="drag-block-input" >
                                                @foreach($question->matchingQuestionAnswers as $option)
                                                    @if(  $option->correct_answer_id !== null )
                                                        @if($option->correct_answer_id == $group->id)
                                                            <div class="bg-light-grey base border-light-grey border-2
                                                                 rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18 pdf-80 pdf-minh-40"
                                                            >
                                                                <span class="mr-3 flex items-center pdf-align-center" >{{ $option->answer }}</span>
                                                                <div class="w-4">
                                                                    <x-icon.drag-pdf/>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </x-dropzone>
                                    </td>
                                    @endif
                                @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

        @endif
        @if($question->subtype == 'Matching')
            <div class="flex flex-col space-y-1 matching prevent-pagebreak">
                <div class="flex flex-col space-y-3">
                    <table class="no-border question-no-break-matching-option" border="0" >
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <tr class="no-border" style="border: 0;">
                                <td class="no-border" style="width: 400px;height: 70px;">
                                    <div class="w-full label-dropzone" style="height: 50px;">
                                            <div class="block w-full h-full py-2 px-4 border-2 border-blue-grey rounded-10
                                                         bg-primary-light font-size-18 bold base leading-5" style="height: 50px;">
                                                        {{ $group->answer }}
                                            </div>
                                    </div>
                                </td>
                                <td class="no-border" style="width:600px;border: 0;">
                                    <div class="flex-1 matching-dropzone w-full" >
                                        <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                    wire:sortable.item="{{ $group->id }}">
                                            <div class="block w-full dropzone-height" selid="drag-block-input">
                                                @foreach($question->matchingQuestionAnswers as $option)
                                                    @if(  $option->correct_answer_id !== null )
                                                        @if($option->correct_answer_id == $group->id)
                                                            <div class="bg-light-grey base border-light-grey border-2
                                                                 rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18 pdf-80 pdf-minh-40"
                                                            >
                                                                <span class="mr-3 flex items-center pdf-align-center" >{{ $option->answer }}</span>
                                                                <div class="w-4">
                                                                    <x-icon.drag-pdf/>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </x-dropzone>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-partials.answer-model-question-container>