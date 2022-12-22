<x-partials.test-opgaven-print-question-container :number="$number" :question="$question">

    <div class="w-full overview" >
{{--        <div class="italic">--}}
{{--            <span>{!! __('test-pdf.true-false_question_explanation') !!}</span>--}}
{{--        </div>--}}
        <div class="items-center" >
            <div class="flex-pdf question-no-break-true-false relative truefalse-container-parent" style="">

{{--                <div class="flex-pdf flex-wrap-pdf overview truefalse-container">--}}
{{--                    @foreach( $question->multipleChoiceQuestionAnswers as $link)--}}
{{--                        <div class="relative true-false-sub">--}}
{{--                            <div class="round-checkbox relative">--}}
{{--                                &nbsp;--}}
{{--                            </div>--}}
{{--                            <label for="link{{ $link->id }}"--}}
{{--                                   class="truefalse-label absolute">--}}
{{--                                <input id="link{{ $link->id }}"--}}
{{--                                       name="Question_{{ $question->id }}"--}}
{{--                                       type="radio"--}}
{{--                                       class="hidden"--}}
{{--                                       value="{{ $link->id }}"--}}
{{--                                       disabled--}}
{{--                                >--}}
{{--                                <span>{!! $link->answer !!}</span> --}}{{-- Juist / onjuist --}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
                <div class="truefalse-text" style="margin-left: 0">
                    {!! $question->converted_question_html  !!}
                </div>
            </div>
        </div>
    </div>
</x-partials.test-opgaven-print-question-container>