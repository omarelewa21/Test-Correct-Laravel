<x-partials.question-container :number="$number" :question="$question">
    <div class="mb-6">
        Lekker relateren (test take)
        Question text here
    </div>

    <x-question.relation-question-grid :viewStruct="$viewStruct" :words="$words"/>

    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
