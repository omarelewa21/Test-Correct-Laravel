<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            <x-input.group for="me" class="w-full disabled mt-4">
                <x-input.textarea
                        wire:model="answer" disabled style="min-height:80px"
                ></x-input.textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.co-learning-question-container>
