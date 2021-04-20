<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div wire:ignore>{!! $question->getQuestionHtml()  !!}</div>
        <div class="mt-4 flex">
            <table class="border-collapse">
                <thead>
                <tr>
                    <th class=""></th>
                    @foreach($questionAnswers as $questionAnswer)
                        <th id="th_{{ $questionAnswer->getKey() }}"
                            class="p-3 font-bold capitalize bg-primary-light base border border-blue-grey table-cell">{!! $questionAnswer->answer !!}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($subQuestions as $subQuestion)
                    <tr id="tr_{{ $subQuestion->getKey() }}"
                        class="bg-white flex table-row flex-row lg:flex-no-wrap lg:mb-0">
                        <td id="td_{{ $subQuestion->getKey() }}"
                            class="p-3 text-center font-bold capitalize bg-primary-light base border border-blue-grey lg:table-cell static">
                            {!! $subQuestion->sub_question !!}
                        </td>
                        @foreach($questionAnswers as $questionAnswer)
                            <td id="td_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                class="w-full lg:w-auto p-3 text-gray-800 text-center border border-b table-cell static">
                                <label id="label_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                       class="@isset($this->answerStruct[$subQuestion->getKey()]) @if($this->answerStruct[$subQuestion->getKey()] == $questionAnswer->getKey()) bg-all-red @endif @endisset"
                                >
                                    <input id="input_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                           wire:model="answer"
                                           type="radio" name="matrix_input_{{$subQuestion->getKey()}}"
                                           value="{{ $subQuestion->getKey() }}:{{ $questionAnswer->getKey() }}"
                                           @isset($this->answerStruct[$subQuestion->getKey()]) @if($this->answerStruct[$subQuestion->getKey()] == $questionAnswer->getKey()) checked="checked" @endif @endisset
                                    >
                                </label>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
