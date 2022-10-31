<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber"
                                           :question="$question">
    <div class="w-full">
        <div class="relative">
            <div class="flex items-start flex-wrap co-learning-completion">
                @foreach($questionTextPartials as $answerIndex => $textPartialArray)
                    @foreach($textPartialArray as $textPartial){!!$textPartial!!}@endforeach
                        <div class="flex flex-col mx-2 mb-1">
                            <span class="bold w-full flex justify-center mb-1">
                            {!! $this->answerOptions[$answerIndex]['answer'] !!}
                            </span>
                            <x-button.true-false-toggle wireModel="answerOptions.{{ $answerIndex }}.rating"
                                                        disabled="{{ !$this->answerOptions[$answerIndex]['answered'] }}"
                            ></x-button.true-false-toggle>
                        </div>
                @endforeach
                @foreach($questionTextPartialFinal as $textPartial){!!$textPartial!!}@endforeach
            </div>
            <span class="hidden -ml-1"></span>
        </div>
    </div>
</x-partials.co-learning-question-container>
