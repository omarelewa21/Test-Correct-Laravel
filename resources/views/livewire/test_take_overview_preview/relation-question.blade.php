<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3 question-no-break-completion" x-data="">

        <div>

            @foreach($this->answer as $wordId => $answer)
                <span class="pdf-student-answers-input relation-question-pdf" >{{ $words->firstWhere('id', $wordId)->answer }}{!! "&nbsp;" !!}</span>
                <span class="pdf-student-answers-label relation-question-pdf">
                    @if($words->firstWhere('id', $wordId)->prefix_text)
                        {{ $words->firstWhere('id', $wordId)->prefix_text }}:
                    @endisset
                    <strong>{{ $words->firstWhere('id', $wordId)->text }}</strong>
                </span>
                <br>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>
