<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3"
         x-data="completionQuestion()"
         x-init="truncateOptionsIfTooLong($el); setTitlesOnLoad($el)"
         @resize.window.debounce.250ms="truncateOptionsIfTooLong($el)"
    >
        <div wire:ignore>
            <x-input.group class="body1 max-w-full flex-col" for="">
                {!! $html !!}
            </x-input.group>
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
