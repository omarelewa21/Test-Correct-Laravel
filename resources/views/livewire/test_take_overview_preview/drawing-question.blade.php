<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        @if($showQuestionText)
            <div class="flex flex-col space-y-3 children-block-pdf questionContainer">
                {!! $question->converted_question_html !!}&nbsp;
            </div>
        @endif
        <div class="mt-3 flex flex-1 flex-col">
            @if($answer != '')
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="600"
                     src="{{$imgSrc}}" alt="">
                <span>{{ $additionalText }}</span>
            @else
                <div class="block w-1/2 border border-blue-grey rounded-10 text-center align-middle" style="min-height:200px; height: 200px;">
                    <span class="inline-block bold mid-grey font-size-18 w-full text-center" style="margin-top: 90px;">{{__('drawing-question.Geen afbeelding')}}</span>
                </div>
            @endif
        </div>
    </div>
</x-partials.answer-model-question-container>
