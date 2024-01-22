<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div x-data=""
         x-init="$el.querySelectorAll('input')
                .forEach(function(el){
                    if(el.value == '') {
                        el.classList.add('border-red')
                    }
                 })
                 "
    >
        <div class="mb-6">
            <span>{!!   $question->convertedQuestionHtml !!}</span>
        </div>

        <x-question.relation-question-grid :viewStruct="$viewStruct" :words="$words"/>
    </div>


</x-partials.overview-question-container>
