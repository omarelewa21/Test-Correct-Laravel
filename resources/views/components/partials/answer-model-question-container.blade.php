@props([
'question',
'number',
'answer'
])

<div class="flex flex-col p-8 sm:p-10 content-section rs_readable">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle cursor-default">{{ $number }}</span>
        </div>

        @if($question->closeable )
            <x-icon.unlocked class="ml-2"/>
        @endif

        <h1 class="inline-block ml-2 mr-6" selid="questiontitle"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>
    <div class="flex flex-1 overview">
        <div class="questionContainer w-full">
            {{ $slot }}
        </div>
    </div>
</div>

