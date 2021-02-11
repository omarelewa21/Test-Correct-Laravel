<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3">
        <div>{!! $question->getQuestionHtml() !!}</div>
        <span wire:model="answerStruct">{!! json_encode($answerStruct) !!}</span>
        <div class="flex flex-col " wire:sortable="updateOrder">
            @foreach($answerStruct as $answer)
                <x-drag-item sortId="{{ $answer->value }}"
                             wireKey="option-{{ $answer->value }}">
                    value:{{ $answer->value }} order:{{$answer->order}}</x-drag-item>
            @endforeach

        </div>
        @foreach($question->rankingQuestionAnswers as $option)
            <x-drag-item sortId="{{ $option->id }}"
                         wireKey="option-{{ $option->id }}">
                {{ $option->answer }} {{ $option->id }}</x-drag-item>
        @endforeach
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
