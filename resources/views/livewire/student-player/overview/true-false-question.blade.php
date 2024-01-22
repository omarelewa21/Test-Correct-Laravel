<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full overview">
        <div class="flex space-x-4 items-center">
            <div class="inline-flex bg-off-white border @if(!$this->answered) border-all-red @else border-blue-grey @endif rounded-lg overview truefalse-container">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)

                    <label for="link{{ $link->id }}"
                           class="bg-off-white border border-off-white rounded-lg trueFalse bold disabled
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                           {!! ($this->answer == $link->id) ? 'active' :'' !!}">
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
                        <div class="@if(!$this->answered) bg-all-red @else bg-blue-grey @endif"
                             style="width: 1px; height: 30px; margin-top: 3px"></div>
                    @endif
                @endforeach
            </div>
            <div class="questionhtml">
                {!! $question->converted_question_html  !!}
            </div>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</x-partials.overview-question-container>