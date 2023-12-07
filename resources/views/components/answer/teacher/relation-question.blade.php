<span class="flex items-start flex-col ">
    <span>lekker relateren ({{ $studentAnswer ? 'Student Answer' : 'Answer Model' }})</span>

    <x-question.relation-answer-grid :answer-struct="$answerStruct" :student-answer="$studentAnswer" :show-toggles="$showToggles ?? false"
    />

</span>
