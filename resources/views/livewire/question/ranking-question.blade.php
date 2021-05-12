<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3"
         x-on:add-width-to-drag-item.window="
             var rankingBody = document.querySelector('#rq{{$question->getKey()}}')
             rankingBody.querySelectorAll('.drag-item').forEach(function(item) {
                item.style.maxWidth = rankingBody.offsetWidth+1+'px';
             });
        "
         wire:init="dispatchDragItemWidth()"
    >
        <div questionHtml wire:ignore>{!! $question->getQuestionHtml() !!}</div>
        <div>
            <span>{!! __('test_take.instruction_ranking_question') !!}</span>
        </div>
        <div id="rq{{ $question->getKey() }}" class="flex flex-col max-w-max space-y-2 ranking" wire:sortable="updateOrder" wire:model="answerStruct">
            @foreach($answerStruct as $answer)
                <x-drag-item id="ranking-{{$answer->value}}" sortId="{{ $answer->value }}"
                             wireKey="option-{{ $answer->value }}">
                        {{ $answerText[$answer->value] }}
                </x-drag-item>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
