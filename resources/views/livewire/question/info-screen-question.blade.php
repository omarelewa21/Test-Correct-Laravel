<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div class="flex flex-col body1 space-y-3">
            <span>{!! __('test_take.info_screen_question_bottom_text') !!}</span>
            <div>
                {!! $question->getQuestionHtml() !!}
            </div>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
