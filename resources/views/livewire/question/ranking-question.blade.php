<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3">
        <div questionHtml wire:ignore>{!! $question->getQuestionHtml() !!}</div>
        <div>
            <span>{!! __('test_take.instruction_ranking_question') !!}</span>
        </div>
        <div class="flex flex-col max-w-max space-y-2 ranking" wire:sortable="updateOrder" wire:model="answerStruct">
            @foreach($answerStruct as $answer)
                <x-drag-item id="ranking-{{$answer->value}}" sortId="{{ $answer->value }}"
                             wireKey="option-{{ $answer->value }}">
                        {{ $answerText[$answer->value] }}
                </x-drag-item>
            @endforeach
        </div>
    </div>
    @push('scripts')
        <script>

        </script>
    @endpush
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
