<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="italic">
            {{__('test-pdf.multiple_choice_question_explanation')}}
        </div>
        <div class="children-block-pdf">
            {!! $question->converted_question_html  !!}
        </div>
        <div class="mt-4 space-y-2 w-full question-no-break-mc-option">
            @foreach( $this->answerStruct as $key => $value)
                <div class="block mc-radio relative " style="vertical-align: top">
                    <div class="square-checkbox block relative align-middle">
                        &nbsp;
                    </div>
                    <label
                            for="link{{ $value }}"
                            class="inline-block w-1/2 px-2 py-1 border-2 border-blue-grey rounded-10 base
                            multiple-select-label align-top"
                    >
                        <input
                                id="link{{ $value }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                        >
                        <div class="w-full multiple-select-text">
                            <div class="mc-radio-label-pdf">{!! $this->answerText[$key] !!}</div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</x-partials.test-print-question-container>
