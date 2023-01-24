<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">
            <div class="w-full">
{{--                <div class="italic">--}}
{{--                    {{ __('test_take.info_screen_question_bottom_text') }}--}}
{{--                </div>--}}
                <div class="flex flex-col body1 space-y-3">
                    <div class="questionContainer children-block-pdf">
                        {!! $question->converted_question_html !!}
                    </div>
                </div>
            </div>
</x-partials.test-opgaven-print-question-container>
