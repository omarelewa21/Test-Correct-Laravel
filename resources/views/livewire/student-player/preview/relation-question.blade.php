<x-partials.question-container :number="$number" :question="$question">
    <div class="mb-6">
        <span>{!!   $question->convertedQuestionHtml !!}</span>
    </div>

    <x-question.relation-question-grid :viewStruct="$viewStruct" :words="$words"/>

    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
