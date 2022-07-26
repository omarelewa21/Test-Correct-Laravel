<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full space-y-3 matching-question">
        <div class="italic">
            {{--            {{__('test-pdf.matching-question-explanation')}} {{-- of classify-question-explanation --}}
        </div>
        <div class="children-block-pdf">
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')
            {{-- maak x aantal vakken, 3 per regel --}}
        <div class="classify-answers-container">
            @foreach($answerOptions as $key => $answer)
                <div>{{$answer}}</div>

            @endforeach
        </div>


        <div class="classify-groups-container">
            @foreach($answerGroups as $group)
                <div>{{$group}}</div>
            @endforeach
        </div>


            {{--                        <div class="flex-wrap-pdf flex-col-pdf classify question-no-break-matching-option" style="margin-top: 40px;">--}}
            {{--                            <div class="flex-row-pdf space-x-5 classified ">--}}
            {{--                                @php $counter = 0; @endphp--}}
            {{--                                @foreach ($question->matchingQuestionAnswers as $group)--}}
            {{--                                    @if(  $group->correct_answer_id === null )--}}
            {{--                                        @php $counter++; @endphp--}}
            {{--                                        <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"--}}
            {{--                                                    wire:sortable.item="{{ $group->id }}">--}}
            {{--                                            <div class="flex-pdf flex-col-pdf w-full dropzone-height" selid="drag-block-input" >--}}
            {{--                                                @foreach($question->matchingQuestionAnswers as $option)--}}
            {{--                                                    @if(  $option->correct_answer_id !== null )--}}
            {{--                                                        @if($option->correct_answer_id == $group->id)--}}
            {{--                                                            <div class="bg-light-grey base border-light-grey border-2--}}
            {{--                                                                 rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18 pdf-80 pdf-minh-40"--}}
            {{--                                                            >--}}
            {{--                                                                <span class="mr-3 flex items-center pdf-align-center" >{{ $option->answer }}</span>--}}
            {{--                                                                <div class="w-4">--}}
            {{--                                                                    <x-icon.drag-pdf/>--}}
            {{--                                                                </div>--}}
            {{--                                                            </div>--}}
            {{--                                                        @endif--}}
            {{--                                                    @endif--}}
            {{--                                                @endforeach--}}
            {{--                                            </div>--}}
            {{--                                        </x-dropzone>--}}
            {{--                                        @if($counter %3 == 0)--}}
            {{--                                            </div>--}}
            {{--                                            <div class="flex-row-pdf space-x-5 classified">--}}
            {{--                                        @endif--}}
            {{--                                    @endif--}}
            {{--                                @endforeach--}}
            {{--                            </div>--}}
            {{--                        </div>--}}

        @endif
        @if($question->subtype == 'Matching')
            <div class="matching prevent-pagebreak">
                <div class="">
                    <table class="matching-table question-no-break-matching-option" border="0">

                        @foreach ($shuffledAnswerSets['LEFT'] as $index => $left)
                            @php $right = $shuffledAnswerSets['RIGHT'][$index] @endphp
                            <tr>
                                <td class="matching-textbox-container-left">
                                    <div class="matching-textbox-left">
                                        {{$left}}
                                        <span class="matching-dot-left"></span>
                                    </div>
                                </td>
                                <td class="matching-textbox-container-right">
                                    <div class="matching-textbox-right">
                                        {{$right}}
                                        <span class="matching-dot-right"></span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-partials.test-print-question-container>