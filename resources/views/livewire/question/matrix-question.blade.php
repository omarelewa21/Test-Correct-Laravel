<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div wire:ignore>{!! $question->getQuestionHtml()  !!}</div>
        <div class="mt-4 flex">
            <div>
                <table class="border-collapse w-full">
                    <thead>
                    <tr>
                        <th class="p-3 font-bold uppercase bg-gray-200 text-gray-600 border border-gray-300 hidden lg:table-cell"></th>
                        @foreach($questionAnswers as $qa)
                            <th class="p-3 font-bold uppercase bg-gray-200 text-gray-600 border border-gray-300 hidden lg:table-cell">{!! $qa->answer !!}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subQuestions as $sq)
                        <p>{!! $sq->sub_question !!}</p>
                        <tr class="bg-white lg:hover:bg-gray-100 flex lg:table-row flex-row lg:flex-row flex-wrap lg:flex-no-wrap mb-10 lg:mb-0">
                            <td class="w-full lg:w-auto font-bold uppercase bg-gray-200 text-gray-600 border border-gray-300 border-b block lg:table-cell relative lg:static">
                                {!! $sq->sub_question !!}
                            </td>
                            @foreach($questionAnswers as $qa)
                                <td class="w-full lg:w-auto p-3 text-gray-800 text-center border border-b block lg:table-cell relative lg:static">
                                    <label>
                                        <input type="radio" name="matrix_input{{$question->getKey()}}">
                                        {{ $loop->parent->iteration }}.{{$loop->iteration}}
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
