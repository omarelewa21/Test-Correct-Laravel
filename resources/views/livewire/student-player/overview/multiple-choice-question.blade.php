<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full flex flex-col">
        <div> {!! $question->converted_question_html  !!}</div>
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $this->shuffledKeys as $value)
                <div class="flex items-center flex-col">
                    <label
                            for="link{{ $value }}"
                            class="relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between
                                        {!! ($this->answerStruct[$value] == 1) ? 'active' : 'disabled' !!}"
                    >
                        <input
                                id="link{{ $value }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                        >
                        <div>{!! $this->answerText[$value] !!}</div>
                        <div class="{!! ($this->answerStruct[$value] == 1) ? '' :'hidden' !!}">
                            <x-icon.checkmark></x-icon.checkmark>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</x-partials.overview-question-container>
