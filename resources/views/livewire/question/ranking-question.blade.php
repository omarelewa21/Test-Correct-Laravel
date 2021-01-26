<div class="flex flex-col p-8 sm:p-10 content-section" x-data="{ showMe: false }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>

    <div class="flex flex-1">
        <div class="w-full space-y-3">
            <div>{!! $question->getQuestionHtml() !!}</div>
            <div class="flex flex-col max-w-max" wire:sortable="updateOrder">
                @foreach($question->rankingQuestionAnswers as $option)
                    <x-drag-item sortId="{{ $option->id }}" wireKey="option-{{ $option->id }}" >{{ $option->answer }}</x-drag-item>
                @endforeach
            </div>
        </div>
    </div>
</div>

