@props([
'question',
])

<div class="flex flex-col p-8 sm:p-10 content-section rs_readable relative" id="answer-container">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $this->answered ? 'complete': 'incomplete' !!}">
            <span class="align-middle cursor-default">{{ $this->questionNumber }}</span>
        </div>
        @if($question->type !== 'InfoscreenQuestion')
            <h4 class="inline-block ml-2">  {{__('co-learning.answer')}} {{ $this->answerNumber }}:</h4>
        @endif

        <h1 class="inline-block ml-2 mr-6"
            selid="questiontitle">{{ $question->type_name }}</h1>
        @if($question->type !== 'InfoscreenQuestion')
            <h4 class="inline-block">max. {{ $question->score }} pt</h4>
        @endif
        @if ($this->answered)
            @if($this->isQuestionFullyAnswered())
                <x-answered />
            @else
                <x-partly-answered />
            @endif
        @else
            <x-not-answered />
        @endif
    </div>
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
    <div class="container-border-left student"></div>
</div>

