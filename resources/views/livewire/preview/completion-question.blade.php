<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3">
        <div wire:ignore>
            <x-input.group class="body1 max-w-full" for="">
                {!! $html !!}
            </x-input.group>
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
