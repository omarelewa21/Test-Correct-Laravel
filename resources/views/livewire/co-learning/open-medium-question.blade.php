<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            <x-input.group for="me" class="w-full disabled mt-4">
                <div class="border border-light-grey p-4 rounded-10 h-fit">{{$this->answer}}</div>
            </x-input.group>
        </div>
    </div>
</x-partials.co-learning-question-container>