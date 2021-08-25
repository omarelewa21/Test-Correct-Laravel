<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3"
         x-data="{}"
         x-init="truncateOptionsIfTooLong($el); setTitlesOnLoad($el)"
         @resize.window.debounce.250ms="truncateOptionsIfTooLong($el)"
    >
        <div questionHtml wire:ignore>
            <x-input.group class="body1 max-w-full" for="">
                {!! $html !!}
            </x-input.group>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
