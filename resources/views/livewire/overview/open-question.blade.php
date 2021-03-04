<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="Typ jouw antwoord" class="w-full disabled mt-4">
                <x-input.textarea
                        wire:model="answer" disabled style="min-height:80px"
                ></x-input.textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.overview-question-container>

