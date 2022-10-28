<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber"
                                           :question="$question">
    <div class="w-full">
        <div class="relative">
            <div class="flex items-start">
                @foreach($questionTextPartials as $answerIndex => $textPartial)

                        <span class=""> {!! $textPartial !!} </span>
                        <div class="flex flex-col mx-2">
                            <span class="bold w-full flex justify-center">
                            {!! $this->answerOptions[$answerIndex]['answer'] !!}
                            </span>
                            <x-button.true-false-toggle wireModel="answerOptions.{{ $answerIndex }}.rating"
                                                        disabled="{{ !$this->answerOptions[$answerIndex]['answered'] }}"
                            ></x-button.true-false-toggle>
                        </div>
                @endforeach
                <span>{!! $QuestionTextPartialFinal !!}</span>
            </div>

        </div>
    </div>
</x-partials.co-learning-question-container>
