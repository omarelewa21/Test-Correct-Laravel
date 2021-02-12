<div class="flex flex-col p-8 sm:p-10 content-section">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>

    <div class="flex flex-1 flex-col space-y-2">
        <div>{!! $question->getQuestionHtml() !!}</div>
        <div class="flex flex-col max-w-min space-y-2">
            @foreach($answerStruct as $answer)
                <x-drag-item-disabled sortId="{{ $answer->value }}"
                             wireKey="option-{{ $answer->value }}">
                    {{ $answerText[$answer->value] }}
                </x-drag-item-disabled>
            @endforeach
        </div>
    </div>
</div>

