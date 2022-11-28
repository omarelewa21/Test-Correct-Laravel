<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full"
         x-data="{ }"
         >

        <div class="flex-col space-y-3">
            <div class="children-block-pdf">
                {!! $question->converted_question_html !!}
            </div>
            <x-input.group wire:ignore class="w-full question-no-break-open-short">
                <x-input.mock-textarea-answered :question="$question"
                                       disabled style="min-height:80px"
                                       :answer="$answer"
                ></x-input.mock-textarea-answered>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.answer-model-question-container>


