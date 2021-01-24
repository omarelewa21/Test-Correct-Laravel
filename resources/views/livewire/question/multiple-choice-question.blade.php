<div class="flex flex-col p-8 sm:p-10 content-section"  x-show="'{{ $number }}' == current">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>
    <div>

        {!! $question->getQuestionHtml()  !!}

        <div class="mt-4 space-y-4">
            @foreach( $question->multipleChoiceQuestionAnswers as $link)
                <div class="flex items-center">
                    <input
                        wire:model="answer"
                        id="link{{ $link->id }}"
                        name="Question_{{ $question->id }}"
                        type="radio"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                        value="{{ $link->id }}"
                    >
                    <label
                        for="link{{ $link->id }}"
                        class="ml-3 block text-sm font-medium text-gray-700"
                    >
                        {!! $link->answer !!}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
