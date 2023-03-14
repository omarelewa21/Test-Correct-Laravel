<div class="flex flex-col gap-2 max-w-max">
    @foreach($answerStruct as $answerOption)
        <x-drag-item-disabled sortId="{{ $answerOption->ranking_question_answer_id }}"
                              wireKey="option-{{ $answerOption->ranking_question_answer_id }}">
            {{ html_entity_decode($answerOption->answer) }}
        </x-drag-item-disabled>
    @endforeach
</div>