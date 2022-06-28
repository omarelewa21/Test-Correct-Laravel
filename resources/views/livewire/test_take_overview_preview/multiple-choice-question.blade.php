<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        {!! $question->converted_question_html  !!}
        <div class="mt-4 space-y-2 w-1/2 question-no-break-mc-option">
            @foreach( $this->shuffledKeys as $value)
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
                        <div style="display: inline;">{!! $this->answerText[$value] !!}</div>
                        <div class="{!! ($this->answerStruct[$value] == 1) ? '' :'hidden' !!}" style="float:right;">
                            <x-icon.checkmark-pdf ></x-icon.checkmark-pdf>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>
