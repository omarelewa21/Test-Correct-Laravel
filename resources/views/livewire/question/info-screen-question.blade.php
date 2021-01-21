<div class="flex flex-col place-content-between">
    {{-- The Master doesn't talk, he acts. --}}

    {!! $question->getQuestionHtml() !!}


    <div class="italic">{!! __('test_take.info_screen_question_bottom_text') !!}</div>
</div>
