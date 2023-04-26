<x-partials.test-print-question-container :number="$number" :question="$question">
            <div class="w-full">
                <div class="italic">
                    {{ __('test_take.info_screen_question_bottom_text') }}
                </div>
                <div class="flex flex-col body1 space-y-3 w-full">
                    <div class="questionContainer children-block-pdf questionhtml" >
                        {!! $question->converted_question_html !!}
                    </div>
                </div>
            </div>
</x-partials.test-print-question-container>
