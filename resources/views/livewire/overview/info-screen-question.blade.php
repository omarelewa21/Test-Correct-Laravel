<div class="flex flex-col p-8 sm:p-10 content-section" >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
    </div>

    <div class="flex flex-1">
        <div class="w-full">
            <div class="flex flex-col body1 space-y-3">
                <span>{!! __('test_take.info_screen_question_bottom_text') !!}</span>
                <div    >
                    {!! $question->getQuestionHtml() !!}
                </div>
            </div>
        </div>
    </div>
</div>
