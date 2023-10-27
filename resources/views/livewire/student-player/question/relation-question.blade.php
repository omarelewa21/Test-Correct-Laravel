<x-partials.question-container :number="$number" :question="$question">
    <div>Lekker relateren</div>

    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
