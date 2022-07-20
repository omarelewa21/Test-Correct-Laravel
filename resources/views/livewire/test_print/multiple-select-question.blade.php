<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">
        <div class="w-full">
            <div class="italic">
                {{__('test-pdf.multiple_choice_question_explanation')}}
            </div>
            <div class="children-block-pdf">
                {!! $question->converted_question_html  !!}
            </div>
            <div class="space-y-2 w-full question-no-break-mc-option">
                @foreach( $this->answerStruct as $key => $value)
                    <div class="block mc-radio relative">
                        <div class="square-checkbox relative">
                            &nbsp;
                        </div>
                        <label
                                for="link{{ $value }}"
                                class="absolute inline-block px-2 py-1 base
                            multiple-select-label justify-between"
                        >
                            <input
                                    id="link{{ $value }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $value }}"
                            >
                            <div class="w-full multiple-select-text">
                                <div class="mc-radio-label-pdf">
                                    <span style="-webkit-text-stroke-width: 0.30px">{{$this->characters[$this->charCounter++]}}.</span> {{-- webkit-text-stroke-width == bold text replacement --}}
                                    {!! $this->answerText[$key] !!}
                                </div>
                            </div>

                        </label>
                    </div>
                @endforeach
            </div>
        </div>
</x-partials.test-print-question-container>

