@props([
'question',
'number',
'pdf_type' => 'answer_model'
])

<div class="test-print-pdf flex flex-col p-8 sm:p-10 content-section rs_readable page-no-break" >
    <div class="tp-question-title inline-flex-pdf justify-between items-center question-indicator border-bottom mb-2 question-indicator-no-break"  style="position:relative;">
        <div class="flex-pdf flex-wrap-pdf">
            <div class="inline-flex-pdf question-number rounded-full text-center justify-center items-center complete" >
                <span class="align-middle cursor-default">{{ $number }}</span>
            </div>

            <h1 class="inline-block-pdf ml-2 mr-6" selid="questiontitle" > {!! __($question->caption) !!} </h1>
            @if($question->type != 'InfoscreenQuestion')
                <h4 class="inline-block-pdf">{{ $question->score }} pt</h4>
            @endif
            @if($this->group)
                <h1  class="inline-flex ml-2">{{ $this->group->name }}</h1>
            @endif
        </div>
    </div>
    @if($this->group)
        <div class="mb-5" >{!! $this->group->question->converted_question_html !!}</div>
    @endif
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
</div>

