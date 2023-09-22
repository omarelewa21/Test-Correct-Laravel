<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full flex flex-col">
        <div questionHtml wire:ignore>{!! $question->converted_question_html  !!}</div>
        <div class="mt-4 space-y-2 w-1/2">

            @foreach( $this->shuffledKeys as $value)
                <div id="mc_c_{{$value}}" wire:key="mc_c_{{$value}}" class="flex items-center flex-col">
                    <label id="mc_c_label_{{$value}}" wire:key="mc_c_label_{{$value}}"
                            for="link{{ $question->id . '-' . $value }}"
                            class=" relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between {!! ($this->answerStruct[$value] == 1) ? 'active' :'' !!}
                                    ">
                        <input
                                wire:model="answer"
                                id="link{{ $question->id . '-' . $value }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $value }}"
                                selid="testtake-radiobutton"
                        >
                        <div selid="testtake-radiobutton" id="mc_c_answertext_{{$value}}" wire:key="mc_c_answertext_{{$value}}">{!! $this->answerText[$value] !!}</div>
                        <div id="mc_c_checkmark_{{$value}}" wire:key="mc_c_checkmark_{{$value}}" class="{!! ($this->answerStruct[$value] == 1) ? '' :'hidden' !!}">
                            <x-icon.checkmark/>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
