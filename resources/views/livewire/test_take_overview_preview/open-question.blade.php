<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            @if($showQuestionText)
                <div class="children-block-pdf questionContainer">
                {!!   $question->converted_question_html !!}&nbsp;
                </div>
            @endif
            <x-input.group for="me" class="w-full disabled mt-4 question-no-break-open-medium">
                <x-input.mock-textarea-answered :question="$question"
                                                disabled style="min-height:80px"
                                                :answer="$answer"
                ></x-input.mock-textarea-answered>
            </x-input.group>
        </div>
        <div style="font-size: 14px; color: #6b7789; margin-top: 0.5rem;">
            {!! $this->getWordCountText() !!}
        </div>
    </div>
</x-partials.answer-model-question-container>

