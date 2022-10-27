<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber" :question="$question" >
    <div class="w-full">
        <div class="relative">
            <div class="flex items-center">
                @foreach($questionTextPartials as $key => $textPartial)
                    <span class="flex items-center">{!! $textPartial !!} <x-button.true-false-toggle wireModel="ratings.{{$key}}"/> </span>
                @endforeach
                <span>{!! $finalQuestionTextPartial !!}</span>
            </div>

            {{ var_dump($ratings) }}

        </div>
    </div>
</x-partials.co-learning-question-container>
