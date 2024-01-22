<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full space-y-3 question-no-break-completion" x-data="">
        <div>
            @foreach($answerStruct as $wordId => $word)
                <span class="pdf-student-answers-input relation-question-pdf" >{{ $word->answer }}{!! "&nbsp;" !!}</span>
                <span class="pdf-student-answers-label relation-question-pdf">
                    @if($word->prefix_text)
                        {{ $word->prefix_text }}:
                    @endif
                    <strong>{{ $word->text }}</strong>
                </span>
                <br>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>
