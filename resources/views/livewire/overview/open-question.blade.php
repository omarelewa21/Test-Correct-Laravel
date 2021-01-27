<div class="flex flex-col p-8 sm:p-10 content-section"  >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!!  __($question->caption) !!}</h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>
    <div class="w-full">
        <div class="relative">
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="Typ jouw antwoord" class="w-full disabled">
                <x-input.textarea
                    wire:model="answer" disabled style="min-height:80px"
                ></x-input.textarea>
            </x-input.group>
        </div>
    </div>
</div>

