<x-partials.question-container :number="$number" :question="$question">

    <div class="w-full">
        {!! $question->getQuestionHtml()  !!}
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $question->multipleChoiceQuestionAnswers as $link)
                <div class="flex items-center mc-radio">
                    <label
                            for="link{{ $link->id }}"
                            class="relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base
                            multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                            justify-between cursor-pointer {!! ($this->answerStruct[(int)$link->id] == 1) ? 'active' :'' !!}"
                    >
                        <input
                                wire:model="answer"
                                id="link{{ $link->id }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $link->id }}"
                        >
                        <div>{!! $link->answer !!}</div>
                        <div class="{!! ($this->answerStruct[(int)$link->id] == 1) ? '' :'hidden' !!}">
                            <x-icon.checkmark class="checkmark transition"></x-icon.checkmark>
                            <x-icon.close class="w-4 h-4 close transition"/>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
