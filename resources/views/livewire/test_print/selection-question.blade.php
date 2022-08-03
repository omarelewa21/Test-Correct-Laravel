<x-partials.test-print-question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 question-no-break-completion" x-data="">
        <div class="italic">
            {{ __('test-pdf.selection-question-explanation') }}
        </div>
        <div class="mt-2">
            @foreach($availableAnswersList as $tag_id => $answers) {{-- loop over all answer sets --}}
            <div class="selection-answers-container">
                <span class="selection-answer-tag-id">{{ $tag_id }}.</span>
                <div class="selection-answers-container-sub">
                    @foreach($answers as $letter => $answer)
                    <span class="selection-answers-container-sub-sub">
                        <span>{{$letter}}. </span>
                        <span>{{$answer}}</span>
                    </span>
                    @endforeach
                </div>

            </div>
            @endforeach

            <x-input.group class="body1 max-w-full flex-col children-block-pdf" for="" x-data="">
                {!! $html  !!}
            </x-input.group>
        </div>
    </div>
</x-partials.test-print-question-container>
