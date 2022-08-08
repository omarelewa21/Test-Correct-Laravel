<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3">
            {!! $question->converted_question_html !!}
        </div>
        <div class="mt-3 flex flex-1 flex-col">
            @if($answer != '')
                @if($this->backgroundImage)
                    <img class="absolute height-240" src="{{$this->backgroundImage}}" width="400">
                @endif
                <img id="drawnImage" class="relative border border-blue-grey rounded-10 @if($this->backgroundImage) height-240 @endif" width="400"
                     src="{{ route('student.drawing-question-answer',$answer) }}?{!! date('Ymdsi') !!}" alt="">
                <span>{{ $additionalText }}</span>
            @else
                <div class="flex flex-1 w-1/2 border border-blue-grey rounded-10 justify-center items-center" style="min-height:200px">
                    <span class="bold mid-grey font-size-18">{{__('drawing-question.Geen afbeelding')}}</span>
                </div>
            @endif
        </div>
    </div>
</x-partials.overview-question-container>
