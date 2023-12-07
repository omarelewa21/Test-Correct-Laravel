<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        @if($showQuestionText)
            <div class="children-block-pdf questionContainer">
            {!! $question->converted_question_html  !!}&nbsp;
            </div>
        @endif
        <div class="mt-4 space-y-2 w-1/2 question-no-break-mc-option">
            @foreach( $this->shuffledKeys as $value)
                @if($this->answerStruct[$value] == 1)
                <div class="block items-center  mc-radio">
                    <label
                            for="link{{ $value }}"
                            class="relative w-full block hover:font-bold p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between
                                        {!! ($this->answerStruct[$value] == 1) ? 'active' : 'disabled' !!}"
                    >
                        <input
                                id="link{{ $value }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                        >
                        <div class="mc-radio-label-pdf">{!! $this->answerText[$value] !!}</div>
                        <div class="{!! ($this->answerStruct[$value] == 1) ? '' :'hidden' !!}" style="float:right;">
                            <x-icon.checkmark-pdf ></x-icon.checkmark-pdf>
                        </div>
                    </label>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>
