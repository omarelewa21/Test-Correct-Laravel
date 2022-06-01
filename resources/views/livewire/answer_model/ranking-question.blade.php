<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="flex flex-1 flex-col space-y-2">
        <div>{!! $question->converted_question_html !!}</div>
        <div class="flex flex-col pdf-100 max-w-max space-y-2">
            @foreach($answerStruct as $answer)
                <x-drag-item-disabled sortId="{{ $answer->value }}"
                                      wireKey="option-{{ $answer->value }}"
                                        class="pdf-mt-2">
                    {{ $answerText[$answer->value] }}
                </x-drag-item-disabled>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>

