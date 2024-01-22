<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3">
        <div>
            <span>{!! __('test_take.instruction_arq') !!}</span>
        </div>
        <div class="flex flex-col space-y-5 xl:space-y-0 xl:flex-row xl:space-x-5">
            <div class="flex flex-1 flex-col space-y-6 questionContainer questionhtml">
                {!! $question->converted_question_html !!}
            </div>
            <div class="flex flex-1 flex-col">
                <div>
                    <div class="px-5 space-x-4 text-base bold flex flex-row">
                        <span class="w-16">{{__('test_take.option')}}</span>
                        <span class="w-20">{{__('test_take.thesis')}} 1</span>
                        <span class="w-20">{{__('test_take.thesis')}} 2</span>
                        <span class="w-10">{{__('test_take.reason')}}</span>
                    </div>
                </div>
                <div class="divider my-2"></div>
                <div class="space-y-2">
                    @foreach( $question->multipleChoiceQuestionAnswers as $loopCount => $link)
                        <label
                                class="flex p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                        {!! ($this->answer == $link->id) ? 'active' : 'disabled' !!}"
                                for="link{{ $link->id }}">
                            <input
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $link->id }}"
                            >
                            <span class="w-16 mr-4">{{ __($this->arqStructure[$loopCount][0]) }}</span>
                            <span class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][1]) }}</span>
                            <span class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][2]) }}</span>
                            <span class="max-w-max">{{ __($this->arqStructure[$loopCount][3]) }}</span>
                            <div class="ml-auto   {!! ($this->answer == $link->id) ? '' :'hidden' !!}">
                                <x-icon.checkmark/>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</x-partials.overview-question-container>
