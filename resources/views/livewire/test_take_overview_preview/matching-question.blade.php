<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full space-y-3 matching-question">
        @if($showQuestionText)
            <div class="children-block-pdf questionContainer">
                {!!   $question->converted_question_html !!}&nbsp;
            </div>
        @endif
        @if($question->subtype == 'Classify')
            <div class="flex-wrap-pdf flex-col-pdf classify " style="margin-top: 40px;">
                <div class="flex-row-pdf space-x-5 classified question-no-break-matching-option">
                    @php $counter = 0; @endphp
                    @foreach ($this->getMatchingQuestionAnswers() as $group)
                        @if(  $group->correct_answer_id === null )
                            @php $counter++; @endphp
                            <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div class="flex flex-col w-full dropzone-height" selid="drag-block-input">
                                    @foreach($shuffledAnswers as $option)
                                        @if(  $option->correct_answer_id !== null )
                                            @if($answerStruct[$option->id] == $group->id)
                                                <div class="bg-light-grey base border-light-grey border-2
                                                                 rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item  bold font-size-18 pdf-80 pdf-minh-40"
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
                            @if($counter %3 == 0)
                                </div>
                                <div class="flex-row-pdf space-x-5 classified">
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        @if($question->subtype == 'Matching')
            <div class="flex flex-col space-y-1 matching">
                <div class="matching-question-answers-pdf-unused">
                    @foreach($shuffledAnswers as $option)
                        @if(  $option->correct_answer_id !== null )
                            @if($answerStruct[$option->id] === '')
                                  <div class="matching-question-drag-item">
                                      {{ $option->answer }}
                                  </div>
                            @endif
                        @endif
                    @endforeach
                </div>
                <div class="flex flex-col space-y-3">
                    <table class="no-border matching-question-answers-pdf" border="0" >
                        @foreach ($this->getMatchingQuestionAnswers() as $group)
                            @if(  $group->correct_answer_id === null )
                                <tr>
                                    <td class="left-side-td">
                                        <div>
                                            <span>
                                                {{ $group->answer }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="right-side-td">
                                        @foreach($shuffledAnswers as $option)
                                            @if(  $option->correct_answer_id !== null )
                                                @php
                                                    logger($answerStruct, [$answerStruct[$option->id], $option->id, $group->id]);
                                                @endphp
                                                @if(!in_array($group->id, $answerStruct))
                                                    <div style="padding: 6px 16px; ">&nbsp;</div>
                                                    @break
                                                @endif
                                                @if($answerStruct[$option->id] == $group->id)
                                                    <div>
                                                        <span >{{ $option->answer ?? "&nbsp;" }}</span>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
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