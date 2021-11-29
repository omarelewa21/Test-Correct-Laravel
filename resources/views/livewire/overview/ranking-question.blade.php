<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="flex flex-1 flex-col space-y-2">
        <div>{!! $question->converted_question_html !!}</div>
        <div class="flex flex-col max-w-max space-y-2">
            @foreach($answerStruct as $answer)
                <x-drag-item-disabled sortId="{{ $answer->value }}"
                                      wireKey="option-{{ $answer->value }}">
                    {{ $answerText[$answer->value] }}
                </x-drag-item-disabled>
            @endforeach
        </div>
    </div>
</x-partials.overview-question-container>

