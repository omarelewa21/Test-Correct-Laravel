<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="">
        <div class="italic">
            {{__('test-pdf.ranking-question-explanation')}}
        </div>
        <div class="children-block-pdf">
            {!! $question->converted_question_html !!}
        </div>
        <div class="pdf-100 max-w-max space-y-2 question-no-break-ranking-option">
            @foreach($answerStruct as $answer)
                <div class="ranking-answer">
                    <div class="square-checkbox">
                        &nbsp;
                    </div>
                    <div class="ranking-answer-textbox base "
                    >
                        <span class="mr-3 flex items-center pdf-align-center" >{{ $answerText[$answer->value] }}</span>
                    </div>
                </div>

            @endforeach
        </div>
    </div>
</x-partials.test-print-question-container>

