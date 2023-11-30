<x-partials.question-container :number="$number" :question="$question">
    <div>Lekker relateren (test take)</div>

    @json($answerStruct)

    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
