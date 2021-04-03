<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3">
            {!! $question->getQuestionHtml() !!}
        </div>
        <div class="mt-3 flex flex-1 flex-col">
            @if($answer != '')
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="400"
                     src="{{ route('student.drawing-question-answer',$answer) }}?{!! date('Ymdsi') !!}" alt="">
                <span>{{ $additionalText }}</span>
            @else
                <div class="flex flex-1 w-1/2 border border-blue-grey rounded-10 justify-center items-center">
                    <span class="bold mid-grey font-size-18">{{ __("drawing-question.Geen afbeelding") }}</span>
                </div>
            @endif
        </div>
    </div>
</x-partials.overview-question-container>
