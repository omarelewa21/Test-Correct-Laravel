@props([
'question',
'number',
'answer'
])

<div class="flex flex-col p-8 sm:p-10 content-section rs_readable relative">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $this->answered ? 'complete': 'incomplete' !!}">
            <span class="align-middle cursor-default">{{ $number }}</span>
        </div>

        @if($question->closeable && !$this->closed)
            <x-icon.unlocked class="ml-2"/>
        @elseif($this->closed)
            <x-icon.locked class="ml-2"/>
        @endif

        <h2 class="inline-block ml-2 mr-6" selid="questiontitle"> {!! __($question->caption) !!} </h2>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answered)
            @if($this->isQuestionFullyAnswered())
                <x-answered/>
            @else
                <x-partly-answered/>
            @endif
        @else
            <x-not-answered/>
        @endif
    </div>
    <div class="flex flex-wrap">
        <x-attachment.student-buttons-container :questionAttachmentsExist="true" :question="$question"  :blockAttachments="false"/>
    </div>
    <div class="flex flex-1 overview">
        @if($question->closeable || ( !is_null($question->groupQuestion) && $question->groupQuestion->closeable) )
            @if($this->closed)
                <span>{{__('test_take.question_closed_text')}}</span>
            @else
                <span>{{__('test_take.question_closeable_text')}}</span>
            @endif
        @else
            <div class="questionContainer w-full">
                {{ $slot }}
            </div>
        @endif
    </div>
    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
</div>

