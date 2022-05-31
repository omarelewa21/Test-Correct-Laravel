<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full"
         x-data="{ }"
         >

        <div class="flex-col space-y-3">
            <div>
                {!! $question->converted_question_html !!}
            </div>
            <x-input.group wire:ignore class="w-full">
                <x-input.mock-textarea :question="$question"
                                       disabled style="min-height:80px"
                ></x-input.mock-textarea>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.answer-model-question-container>


