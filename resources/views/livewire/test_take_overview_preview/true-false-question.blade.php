<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full overview">
        <div class="flex-pdf flex-wrap-pdf space-x-4 items-center">
            <div class="inline-block-pdf question-no-break-true-false">
                <div class="my-2 flex-pdf flex-wrap-pdf bg-off-white border @if(!$this->answered) border-all-red @else border-blue-grey @endif rounded-lg overview truefalse-container">
                    @foreach( $question->multipleChoiceQuestionAnswers as $link)

                        <label for="link{{ $link->id }}"
                               class="bg-off-white border border-off-white rounded-lg trueFalse bold disabled
                                          @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                               {!! ($this->answer == $link->id) ? 'active' :'' !!}" style="display: inline;position: relative;top:7px;">
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
                            <div class="flex-pdf @if(!$this->answered) bg-all-red @else bg-blue-grey @endif"
                                 style="width: 1px; height: 30px; margin-top: 3px"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            @if($showQuestionText)
                <div class="inline-block-pdf children-block-pdf w-full">
                    {!! $question->converted_question_html  !!}&nbsp;
                </div>
            @endif
        </div>
    </div>
</x-partials.answer-model-question-container>