<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full overview" >
        <div class="flex-pdf flex-wrap-pdf space-x-4 items-center">
            <div class="flex-pdf flex-wrap-pdf bg-off-white border  border-blue-grey  rounded-lg overview truefalse-container" style="height: 38px;">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)

                    <label for="link{{ $link->id }}"
                           class="bg-off-white border border-off-white rounded-lg trueFalse bold disabled
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                           {!! ($link->score>0) ? 'active' :'' !!}" style="display: inline;position: relative;top:7px;">
                        <input id="link{{ $link->id }}"
                               name="Question_{{ $question->id }}"
                               type="radio"
                               class="hidden"
                               value="{{ $link->id }}"
                               disabled
                        >
                        <span>{!! $link->answer !!}</span>
                    </label>
                    @if($loop->first)
                        <div class="flex-pdf bg-off-white "
                             style="width: 1px; height: 30px; margin-top: 3px;position: relative;top:5px;"></div>
                    @endif
                @endforeach
            </div>
            <div class="flex-pdf flex-wrap-pdf">
                {!! $question->converted_question_html  !!}
            </div>
        </div>
    </div>
</x-partials.answer-model-question-container>