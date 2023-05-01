<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3 children-block-pdf questionContainer">
            {!! $question->converted_question_html !!}&nbsp;
        </div>
        <div class="mt-3 flex flex-1 flex-col">
            @if($answer != '')
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="600"
                     src="{{$imgSrc}}" alt="">
                <span>{{ $additionalText }}</span>
            @else
                <div class="flex flex-1 w-1/2 border border-blue-grey rounded-10 justify-center items-center" style="min-height:200px">
                    <span class="bold mid-grey font-size-18">{{__('drawing-question.Geen afbeelding')}}</span>
                </div>
            @endif
        </div>
    </div>
</x-partials.answer-model-question-container>
