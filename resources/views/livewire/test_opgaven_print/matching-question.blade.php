<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 matching-question">
        <div class="italic">
            @if($question->subtype == 'Classify')
                {{__('test-pdf.classify-question-explanation')}}
            @else
                {{__('test-pdf.matching-question-explanation')}}
            @endif
        </div>
        <div class="children-block-pdf">
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')
            {{-- maak x aantal vakken, 3 per regel --}}
        <div class="classify-answers-container">
            @foreach($answerOptions as $key => $answer)
                <div class="classify-answers-sub">
                    <span class="bold">{{$key}} {!! $answer !!}</span>
                </div>

            @endforeach
        </div>


        <div class="classify-groups-container">
            @foreach($answerGroups as $group)
                <div class="classify-group-block">
                    <div class="classify-group-title">
                        {!! $group !!}
                    </div>

                        <div class="paper-text-area resize-none">
                            @for($i = 0; $i < 2; $i++)
                                <div class="paper-line"/>
                            @endfor
                        </div>
                </div>
            @endforeach
        </div>

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
                                        {!! $left !!}
                                        <span class="matching-dot-left"></span>
                                    </div>
                                </td>
                                <td class="matching-textbox-container-right">
                                    <div class="matching-textbox-right">
                                        {!! $right !!}
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
</x-partials.test-opgaven-print-question-container>