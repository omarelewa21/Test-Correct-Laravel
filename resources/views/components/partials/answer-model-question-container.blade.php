@props([
'question',
'number',
'answer',
'pdf_type' => 'answer_model'
])

<div class="flex flex-col p-8 sm:p-10 content-section rs_readable page-no-break" >
    <div class="question-title flex-pdf flex-wrap-pdf items-center question-indicator border-bottom mb-6 question-indicator-no-break"  style="position:relative;">
        <div class="inline-flex-pdf question-number rounded-full text-center justify-center items-center complete" >
            <span class="align-middle cursor-default">{{ $number }}</span>
        </div>

        @if($question->closeable )
            <x-icon.unlocked class="ml-2"/>
        @endif

        <h1 class="inline-block-pdf ml-2 mr-6" selid="questiontitle" > {!! __($question->caption) !!} </h1>
        <h4 class="inline-block-pdf">{{ $question->score }} pt</h4>
        @if ($this->answered && $pdf_type=='student_test_take')
            @if($this->isQuestionFullyAnswered())
                <p class="ml-auto cta-primary flex space-x-2 items-center inline-block-pdf" style="position:relative;left:800px;">
                    <x-icon.checkmark-pdf class="student_test_take_checkmark_pdf"/>
                    <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.answered') }}</span>
                </p>
            @else
                <p class="ml-auto mid-grey flex space-x-2 items-center inline-block-pdf"  style="position:relative;left:800px;">
                    <x-icon.checkmark-pdf class="student_test_take_checkmark_pdf"/>
                    <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.partly_answered') }}</span>
                </p>
            @endif
        @elseif(!$this->answered && $pdf_type=='student_test_take')
            <p class="ml-auto mid-grey flex space-x-2 items-center inline-block-pdf" style="position:relative;left:800px;">
                <x-icon.close class="student_test_take_checkmark_pdf"/>
                <span class="ml-auto font-size-14 bold align-middle uppercase">{{ __('test_take.not_answered') }}</span>
            </p>
        @endif
    </div>
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
</div>

