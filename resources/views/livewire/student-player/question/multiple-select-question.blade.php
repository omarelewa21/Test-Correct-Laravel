<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full flex flex-col">
        <div questionHtml wire:ignore>{!! $question->converted_question_html  !!}</div>
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $this->shuffledKeys as $value)
                <div id="mc-s-{{ $value.$question->uuid }}" wire:key="mc-s-{{$value.$question->uuid}}"
                     class="flex items-center mc-radio">
                    <label id="mc_s_label_{{$value.$question->uuid}}" wire:key="mc_s_label_{{$value.$question->uuid}}"
                           for="link{{ $value.$question->uuid }}"
                           class="relative w-full flex p-5 border-2 border-blue-grey rounded-10 base
                            multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                            justify-between cursor-pointer {!! ($this->answerStruct[$value] == 1) ? 'active' :'' !!}"
                           @mouseenter="$event.target.querySelector('.close').style.display = 'inline-block'; $event.target.querySelector('.checkmark').style.display = 'none'"
                           @mouseleave="$event.target.querySelector('.close').style.display = 'none'; $event.target.querySelector('.checkmark').style.display = 'inline-block'"
                    >
                        <input
                                wire:model="answer"
                                id="link{{ $value.$question->uuid }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                        >
                        <div id="mc_c_answertext_{{$value.$question->uuid}}"
                             wire:key="mc_c_answertext_{{$value.$question->uuid}}">{!! $this->answerText[$value] !!}</div>
                        <div id="mc_c_icon_{{$value.$question->uuid}}" wire:key="mc_c_icon_{{$value.$question->uuid}}"
                             class="{!! ($this->answerStruct[$value] == 1) ? '' :'hidden' !!}">
                            <x-icon.checkmark class="checkmark transition"></x-icon.checkmark>
                            <x-icon.close class="w-4 h-4 close hidden transition" />
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
