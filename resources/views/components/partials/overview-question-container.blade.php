@props([
'question',
'number',
'answer'
])

<div class="flex flex-col p-8 sm:p-10 content-section" >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer ? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>

        @if($question->closeable && !$this->closed)
            <x-icon.unlocked class="ml-2"/>
        @elseif($this->closed)
            <x-icon.locked class="ml-2"/>
        @endif

        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>
    <div class="flex flex-1 overview">
        @if(!$this->closed)
            {{ $slot }}
        @else
            <span>{{__('test_take.question_closed_text')}}</span>
        @endif
    </div>
</div>

