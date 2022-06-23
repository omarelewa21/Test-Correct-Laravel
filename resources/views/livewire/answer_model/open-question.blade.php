<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            {!!   $question->converted_question_html !!}
            <x-input.group for="me" class="w-full disabled mt-4 question-no-break-open-short">
                <x-input.mock-textarea :question="$question"
                         disabled style="min-height:80px"
                ></x-input.mock-textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.answer-model-question-container>

