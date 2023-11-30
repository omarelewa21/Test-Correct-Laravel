<x-partials.question-container :number="$number" :question="$question">
    <div>Lekker relateren (preview)</div>

    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
