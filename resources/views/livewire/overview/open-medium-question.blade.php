<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full">
        <div class="flex-col space-y-3">
            <div>
                {!! $question->converted_question_html !!}
            </div>
            <x-input.group wire:ignore class="w-full">
                <x-input.rich-textarea  editorId="{{ $editorId }}" wire:model.debounce.2000ms="answer" type="student-preview"></x-input.rich-textarea>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.overview-question-container>


