<x-partials.test-print-question-container :number="$number" :question="$question">
    <div class="w-full open-long">
        <div class="italic">
            {{__('test-pdf.open-long-question-explanation')}}
        </div>
        <div class="flex-col">
            <div class="children-block-pdf questionhhtml questionContainer">
                {!! $question->converted_question_html !!}
            </div>
            <div wire:ignore class="w-full mt-2 question-no-break-open-medium">
                <div class="paper-text-area resize-none overflow-ellipsis">
                    @for($i = 0; $i < 15; $i++)
                        <div class="paper-line"/>
                    @endfor
                </div>
            </div>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.test-print-question-container>


