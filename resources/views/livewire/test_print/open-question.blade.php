<x-partials.test-print-question-container :number="$number" :question="$question">
    <div class="w-full open-short">
        <div class="italic">
            {{__('test-pdf.open-short-question-explanation')}}
        </div>
        <div class="relative">
            <div class="children-block-pdf">
                {!!   $question->converted_question_html !!}
            </div>
            <x-input.group for="me" class="w-full mt-2 question-no-break-open-short">
                <div class="paper-text-area resize-none overflow-ellipsis">
                    @for($i = 0; $i < 6; $i++)
                        <div class="paper-line"/>
                    @endfor
                </div>
            </x-input.group>
        </div>
    </div>
</x-partials.test-print-question-container>

