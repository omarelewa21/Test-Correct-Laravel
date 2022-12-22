<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">
    <div class="">
{{--        <div class="italic">--}}
{{--            {{__('test-pdf.ranking-question-explanation')}}--}}
{{--        </div>--}}
        <div class="children-block-pdf">
            {!! $question->converted_question_html !!}
        </div>
        <div class="pdf-100 max-w-max space-y-2 question-no-break-ranking-option">
            @foreach($answerStruct as $answer)
                <div class="ranking-answer">
                    <div class="square-checkbox bold">
                        <div class="checkbox-number pdf-align-center mr-3">
                            {!!  $loop->index+1 < 10 ? '&nbsp;' . $loop->index+1 : $loop->index+1 !!}
                        </div>
                    </div>
                    <div class="ranking-answer-textbox base " style="padding-bottom: 0; padding-top: 0.25rem"
                    >
                        <span class="mr-3 flex items-center pdf-align-center" >{!! $answerText[$answer->value] !!}</span>
                    </div>
                </div>

            @endforeach
        </div>
    </div>
</x-partials.test-opgaven-print-question-container>

