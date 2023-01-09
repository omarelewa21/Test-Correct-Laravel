@props([
'question',
'number',
'pdf_type' => 'test_print',
])

<div class="test-print-pdf p-8 sm:p-10 content-section rs_readable page-no-break">
    <div class="tp-question-title justify-between items-center question-indicator border-bottom mb-2 question-indicator-no-break"
         style="position:relative;">
        <div class="flex-pdf flex-wrap-pdf">
            <div class="question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle cursor-default">{{ $number }}</span>
            </div>

            <h1 class="inline-block-pdf ml-2 mr-6" selid="questiontitle"> {!! __($question->caption) !!} </h1>
            @if($question->type != 'InfoscreenQuestion')
                <h4 class="inline-block-pdf">{{ $question->score }} pt</h4>
            @endif
        </div>
    </div>
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
    @if($question->questionAttachments)
        <livewire:test-opgaven-print.question-attachments :attachments="$question->attachments" :attachment_counters="$this->attachment_counters ?? []"/>
    @endif
</div>

