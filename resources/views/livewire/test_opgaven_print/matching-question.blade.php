<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 matching-question">
{{--        <div class="italic">--}}
{{--            @if($question->subtype == 'Classify')--}}
{{--                {{__('test-pdf.classify-question-explanation')}}--}}
{{--            @else--}}
{{--                {{__('test-pdf.matching-question-explanation')}}--}}
{{--            @endif--}}
{{--        </div>--}}
        <div class="children-block-pdf">
            {!!   $question->converted_question_html !!}
        </div>
        @if($question->subtype == 'Classify')

            <div class="classify-groups-container">
                @foreach($answerGroups as $index => $group)
                    <div class="classify-group-block" style="width: auto">
                        <div class="classify-group-title-opgave">
                            {!!  $loop->index <= 25 ? $this->characters[$loop->index] : 'A'.$this->characters[$loop->index-26] !!}. {!! $group . (!$loop->last ? ',' : '') !!}
                        </div>

                        {{--                        <div class="paper-text-area resize-none">--}}
                        {{--                            @for($i = 0; $i < 2; $i++)--}}
                        {{--                                <div class="paper-line"/>--}}
                        {{--                            @endfor--}}
                        {{--                        </div>--}}
                    </div>
                @endforeach
            </div>
        <div style="width: 100%; height: 1rem;"></div>
            {{-- maak x aantal vakken, 3 per regel --}}
        <div class="classify-answers-container margin-top-4">
            @foreach($answerOptions as $key => $answer)
                <div class="classify-answers-sub">
                    <span class="bold">{{$key}} {!! $answer !!}</span>
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
                                    <div class="round-checkbox block relative align-middle" style="padding-top: 0.25rem">
                                        <div class="checkbox-number pdf-align-center mr-3">
                                            {!!  $index+1 < 10  ? '&nbsp;' . $index+1 : $index+1 !!}
                                        </div>
                                    </div>
                                    <div class="matching-textbox-left">
                                        {!! $left !!}
{{--                                        <span class="matching-dot-left"></span>--}}
                                    </div>
                                </td>
                                <td class="matching-textbox-container-right">
                                    <div class="round-checkbox block relative align-middle" style="padding-top: 0.25rem">
                                        <div class="checkbox-character pdf-align-center mr-3">
                                            {!!  $index <= 25 ? '&nbsp;' . $this->characters[$index] : 'A'.$this->characters[$index-26] !!}
                                        </div>
                                    </div>
                                    <div class="matching-textbox-right">
                                        {!! $right !!}
{{--                                        <span class="matching-dot-right"></span>--}}
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