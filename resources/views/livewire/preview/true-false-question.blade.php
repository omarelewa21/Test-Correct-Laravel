<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div class="flex space-x-4 items-center">
            <div class="inline-flex bg-off-white border border-blue-grey rounded-lg truefalse-container transition duration-150">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)

                    <label for="link{{ $link->id }}"
                           class="bg-off-white border border-off-white rounded-lg trueFalse bold transition duration-150
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                           {!! ($this->answer == $link->id) ? 'active' :'' !!}">
                        <input wire:model="answer"
                               id="link{{ $link->id }}"
                               name="Question_{{ $question->id }}"
                               type="radio"
                               class="hidden"
                               value="{{ $link->id }}"
                        >
                        <span>{!! $link->answer !!}</span>
                    </label>
                    @if($loop->first)
                        <div class="bg-blue-grey" style="width: 1px; height: 30px; margin-top: 3px"></div>
                    @endif
                @endforeach
            </div>
            <div wire:ignore>{!! $question->converted_question_html  !!}</div>
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
