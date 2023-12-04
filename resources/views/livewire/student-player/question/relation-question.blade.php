<x-partials.question-container :number="$number" :question="$question">
    <div>Lekker relateren (test take)</div>

    <x-question.relation-grid-container :viewStruct="$viewStruct" />

    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
