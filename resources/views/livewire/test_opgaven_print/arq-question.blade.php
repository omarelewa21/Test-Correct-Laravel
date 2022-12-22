<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">

    <div class="w-full space-y-3">
        <div class="italic">
            <span>{!! __('test-pdf.ARQ_question_explanation') !!}</span>
        </div>
        <div class="flex flex-col space-y-5 xl:space-y-0 xl:flex-row xl:space-x-5">
            <div class="flex flex-1 flex-col children-block-pdf">
                {!! $question->converted_question_html !!}
            </div>
            <div class="flex flex-1 flex-col question-no-break-arq-option">
                <div>
                    <div class="text-base bold arq-title-container">
                        <span class="w-12">{{__('test_take.option')}}</span>
                        <span style="width: 10rem; min-width: 9rem;">{{__('test_take.thesis')}} 1</span>
                        <span style="width: 10rem; min-width: 9rem;">{{__('test_take.thesis')}} 2</span>
                        <span class="w-10">{{__('test_take.reason')}}</span>
                    </div>
                </div>
                <div class="divider my-2"></div>
                <div class="space-y-2">
                    @foreach( $question->multipleChoiceQuestionAnswers as $loopCount => $link)
                        <div class="block mc-radio relative">
                            <div class="round-checkbox">
                                &nbsp;
                            </div>
                            <label
                                    class="arq-label absolute inline-block base multiple-choice-question"
                                    for="link{{ $link->id }}">
                                <input
                                        id="link{{ $link->id }}"
                                        name="Question_{{ $question->id }}"
                                        type="radio"
                                        class="hidden"
                                        value="{{ $link->id }}"
                                >
                                <span class="arq-text-container py-1 ">
                                   <span class="w-32"
                                         style="width: 10rem; display:inline-block;">{{ __($this->arqStructure[$loopCount][1]) }}</span>
                                    <span class="w-32"
                                          style="width: 10rem; display:inline-block;">{{ __($this->arqStructure[$loopCount][2]) }}</span>
                                    <span class="max-w-max">{{ __($this->arqStructure[$loopCount][3]) }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-partials.test-opgaven-print-question-container>
