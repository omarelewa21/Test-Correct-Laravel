<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            <div class="children-block-pdf">
            {!!   $question->converted_question_html !!}&nbsp;
            </div>
            <x-input.group for="me" class="w-full disabled mt-4 question-no-break-open-short">
                <x-input.mock-textarea :question="$question"
                         disabled
                ></x-input.mock-textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.answer-model-question-container>

