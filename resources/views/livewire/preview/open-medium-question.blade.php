<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div class="mb-4" wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore>
            <x-input.group class="w-full" label="{!! __('test_take.instruction_open_question') !!}">
                <x-input.rich-textarea  editorId="{{ $editorId }}" wire:model.debounce.2000ms="answer"></x-input.rich-textarea>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>