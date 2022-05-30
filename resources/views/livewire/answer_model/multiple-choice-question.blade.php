<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        {!! $question->converted_question_html  !!}
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $this->answerStruct as $key => $value)
                <div class="flex items-center flex-col">
                    <label
                            for="link{{ $value }}"
                            class="relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between
                                        {!! ($value == 1) ? 'active' : 'disabled' !!}"
                    >
                        <input
                                id="link{{ $value }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                        >
                        <div style="display: inline-flex;">{!! $this->answerText[$key] !!}</div>
                        <div class="{!! ($value == 1) ? '' :'hidden' !!}" style="float:right;">
                            <img class="icon_checkmark_pdf" src="{{public_path('img/icons/icons-checkmark-blue.png')}}">
                            {!! $this->scoreStruct[$key] !!} pt
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>
