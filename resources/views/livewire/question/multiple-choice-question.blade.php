<div>
    {{ get_class($question) }}
    {!! $question->getQuestionHtml()  !!}
    <div class="mt-4 space-y-4">
        @foreach( $question->multipleChoiceQuestionAnswers as $link)
            <div class="flex items-center">
                <input id="link{{ $link->id }}" name="Question_{{ $question->id }}" type="radio"
                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                <label for="link{{ $link->id }}" class="ml-3 block text-sm font-medium text-gray-700">
                    {!! $link->answer !!}
                </label>
            </div>
        @endforeach
    </div>
</div>
