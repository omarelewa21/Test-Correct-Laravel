<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full"
         x-data="{ }"
         >

        <div class="flex-col space-y-3">
            @if($showQuestionText)
                <div class="children-block-pdf questionContainer">
                {!! $question->converted_question_html !!}&nbsp;
                </div>
            @endif
            <x-input.group wire:ignore class="w-full question-no-break-open-short">
                <x-input.mock-textarea-answered :question="$question"
                                       disabled style="min-height:80px"
                                       :answer="$answer"
                ></x-input.mock-textarea-answered>
            </x-input.group>
        </div>
        <div style="font-size: 14px; color: #6b7789; margin-top: 0.5rem;">
            {!! $this->getWordCountText() !!}
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.answer-model-question-container>


