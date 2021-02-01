<x-partials.question-container :number="$number" :q="$q" :question="$question">
    <div class="w-full space-y-3">
        <div>{!! $question->getQuestionHtml() !!}</div>
        <div class="flex flex-col " wire:sortable="updateOrder">
            @foreach($question->rankingQuestionAnswers as $option)
                <x-drag-item sortId="{{ $option->id }}"
                             wireKey="option-{{ $option->id }}">{{ $option->answer }}</x-drag-item>
            @endforeach
        </div>
    </div>
    <x-attachment-modal :attachment="$attachment" />
    <x-notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
