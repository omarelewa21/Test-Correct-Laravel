<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        {!! $question->getQuestionHtml()  !!}
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $question->multipleChoiceQuestionAnswers as $link)
                <div class="flex items-center flex-col">
                    <label
                            for="link{{ $link->id }}"
                            class="relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between
                                        {!! ($this->answer == $link->id) ? 'active' : 'disabled' !!}"
                    >
                        <input
                                id="link{{ $link->id }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $link->id }}"
                        >
                        <div>{!! $link->answer !!}</div>
                        <div class="{!! ($this->answer == $link->id) ? '' :'hidden' !!}">
                            <x-icon.checkmark></x-icon.checkmark>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</x-partials.overview-question-container>
