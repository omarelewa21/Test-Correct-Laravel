@props([
'question',
'number',
'answer',
'pdf_type' => 'answer_model'
])

<div class="flex flex-col p-8 sm:p-10 content-section rs_readable page-no-break" >
    <div class="question-title inline-flex-pdf justify-between items-center question-indicator border-bottom mb-6 question-indicator-no-break"  style="position:relative;">
        <div class="flex-pdf flex-wrap-pdf">
            <div class="inline-flex-pdf question-number rounded-full text-center justify-center items-center complete" >
                <span class="align-middle cursor-default">{{ $number }}</span>
            </div>

            @if($question->closeable )
                <x-icon.unlocked class="ml-2"/>
            @endif

            <h1 class="inline-block-pdf ml-2 mr-6" selid="questiontitle" > {!! __($question->caption) !!} </h1>
            <h4 class="inline-block-pdf">{{ $question->score }} pt</h4>
        </div>
        @if ($this->answered && $pdf_type=='student_test_take')
            @if($this->isQuestionFullyAnswered())
                <div class="cta-primary" style="float:right;margin-top: -20px;">
                    <x-icon.checkmark-pdf class="student_test_take_checkmark_pdf"/>
                    <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.answered') }}</span>
                </div>
            @else
                <div class="cta-primary" style="float:right;margin-top: -20px;">
                    <x-icon.checkmark-pdf class="student_test_take_checkmark_pdf"/>
                    <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.partly_answered') }}</span>
                </div>
            @endif
        @elseif(!$this->answered && $pdf_type=='student_test_take')
            <div class="cta-primary" style="float:right;margin-top: -20px;">
                <x-icon.close class="student_test_take_checkmark_pdf"/>
                <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.not_answered') }}</span>
            </div>
        @endif
    </div>
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
</div>

