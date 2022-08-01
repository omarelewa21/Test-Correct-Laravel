<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="children-block-pdf"> {!! $question->converted_question_html  !!}</div>
        <div class="mt-4 flex">
            <table class="border-collapse question-no-break-matrix matrix-table">
                <thead>
                <tr>
                    <th class=""></th>
                    @foreach($questionAnswers as $questionAnswer)
                        <th id="th_{{ $questionAnswer->getKey() }}"
                            wire:key="th_{{ $questionAnswer->getKey() }}"
                            class="p-3 bold capitalize base table-cell">{!! $questionAnswer->answer !!}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($subQuestions as $subQuestion)
                    <tr class="bg-white flex table-row flex-row ">
                        <td class="p-3 text-center font-bold capitalize base static">
                            {!! $subQuestion->sub_question !!}
                        </td>
                        @foreach($questionAnswers as $questionAnswer)
                            <td class="p-3 text-center table-cell static">
                                <label class="block items-center justify-center"
                                >
{{--                                    <div class="flex w-5 h-5 cursor-pointer rounded-full bg-white items-center justify-center transition border border-primary-hover border-system-secondary">--}}
{{--                                    </div>--}}
                                    <div class="round-checkbox matrix-checkbox block mx-auto relative align-middle">
                                        &nbsp;
                                    </div>
                                </label>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-partials.test-print-question-container>
