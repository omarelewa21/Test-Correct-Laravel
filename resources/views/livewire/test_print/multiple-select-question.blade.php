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
                    <div class="block items-center mc-radio relative">
                        <div class="checkbox-border inline-block border-blue-grey border-dashed border-2 mr-2 rounded-10 w-10 h-10 relative">
                            &nbsp;
                        </div>
                        <label
                                for="link{{ $value }}"
                                class="absolute inline-block w-1/2 hover:font-bold px-2 py-1 border-2 border-blue-grey rounded-10 base
                            multiple-select-question transition ease-in-out duration-150 focus:outline-none
                            justify-between"
                        >
                            <input
                                    id="link{{ $value }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $value }}"
                            >
                            <div class="w-full multiple-select-text-container">
                                <div class="mc-radio-label-pdf">
                                    <span class="font-bold">{{$this->characters[$this->charCounter++]}}.</span>
                                    {!! $this->answerText[$key] !!}
                                </div>
                            </div>

                        </label>
                    </div>
                @endforeach
            </div>
        </div>
</x-partials.test-print-question-container>

