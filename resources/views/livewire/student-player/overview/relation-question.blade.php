<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div>Lekker relateren (overview)</div>

    <x-question.relation-question-grid :viewStruct="$viewStruct" :words="$words"/>

</x-partials.overview-question-container>
