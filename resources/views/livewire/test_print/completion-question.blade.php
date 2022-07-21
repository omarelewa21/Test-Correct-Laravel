<x-partials.test-print-question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 question-no-break-completion" x-data="">
        <div class="italic">
            Explaination of the question
        </div>
        <div>
            @foreach($answerStruct as $index => $answer)
                <span class="completion-question-answer inline-block">
                    <strong>{{$index}}.</strong>
                    <span class="completion-question-answer-sub"></span>
                </span>
            @endforeach

            <x-input.group class="body1 max-w-full flex-col children-block-pdf" for="" x-data="">
                {!! $html  !!}
            </x-input.group>
        </div>
    </div>
</x-partials.test-print-question-container>
