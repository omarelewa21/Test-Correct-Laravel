<x-partials.test-print-question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 question-no-break-completion">
        <div>
            @foreach($answerPlaceholdersList as $iterator)
                <span class="completion-question-answer inline-block">
                    <strong>{{$iterator}}.</strong>
                    <span class="completion-question-answer-sub"></span>
                </span>
            @endforeach
            <br>
            @foreach($answerStruct as $wordId => $word)
                <span class="completion-question-placeholder"><strong>{{ $loop->iteration }}.</strong> .........</span>
                <span class="pdf-student-answers-label relation-question-pdf">
                    @if($word->prefix_text)
                        {{ $word->prefix_text }}:
                    @endif
                    <b>{{ $word->text }}</b>
                </span>
                <br>
            @endforeach
        </div>
    </div>
</x-partials.test-print-question-container>
