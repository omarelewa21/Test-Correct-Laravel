<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="mb-6">
        <span>{!!   $question->convertedQuestionHtml !!}</span>
    </div>

    <x-question.relation-question-grid :viewStruct="$viewStruct" :words="$words"/>

</x-partials.overview-question-container>
