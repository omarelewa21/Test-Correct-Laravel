<div class="flex flex-col gap-2 max-w-max">
    @forelse($answerStruct as $answerOption)
        <x-drag-item-disabled sortId="{{ $answerOption->ranking_question_answer_id }}"
                              wireKey="option-{{ $answerOption->ranking_question_answer_id }}">
            {{ html_entity_decode($answerOption->answer) }}
        </x-drag-item-disabled>
    @empty
        <span>@lang('test_take.not_answered')</span>
    @endforelse
</div>