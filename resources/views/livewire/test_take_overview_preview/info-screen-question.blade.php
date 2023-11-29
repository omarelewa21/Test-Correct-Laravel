<div class="flex flex-col p-8 sm:p-10 content-section question-no-break-info">
    <div class="question-title flex-pdf flex-wrap-pdf items-center question-indicator border-bottom mb-6">
        <div class="inline-flex-pdf question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>

        @if($question->closeable && !$this->closed)
            <x-icon.unlocked class="ml-2"/>
        @elseif($this->closed)
            <x-icon.locked class="ml-2"/>
        @endif

        <h1 class="inline-block ml-2 mr-6" selid="questiontitle"> {!! __($question->caption) !!} </h1>
    </div>

    <div class="flex flex-1">
        @if(!$this->closed)
            <div class="w-full">
                <div class="flex flex-col body1 space-y-3">
                    <span>{!! __('test_take.info_screen_question_bottom_text') !!}</span>
                    @if($showQuestionText)
                        <div class="questionContainer children-block-pdf questionhtml questionContainer">
                            {!! $question->converted_question_html !!}&nbsp;&nbsp;
                        </div>
                    @endif
                </div>
            </div>
        @else
            <span>{{__('test_take.infoscreen_closed_text')}}</span>
        @endif
    </div>
</div>
