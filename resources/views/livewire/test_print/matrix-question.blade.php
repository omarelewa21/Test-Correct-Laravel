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
                            class="p-3 font-bold capitalize bg-primary-light base border border-blue-grey table-cell">{!! $questionAnswer->answer !!}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($subQuestions as $subQuestion)
                    <tr id="tr_{{ $subQuestion->getKey() }}"
                        wire:key="tr_{{ $subQuestion->getKey() }}"
                        class="bg-white flex table-row flex-row lg:flex-no-wrap lg:mb-0">
                        <td id="td_{{ $subQuestion->getKey() }}"
                            wire:key="td_{{ $subQuestion->getKey() }}"
                            class="p-3 text-center font-bold capitalize bg-primary-light base border border-blue-grey lg:table-cell static">
                            {!! $subQuestion->sub_question !!}
                        </td>
                        @foreach($questionAnswers as $questionAnswer)
                            <td id="td_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                wire:key="td_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                class="p-3 text-gray-800 text-center border border-b table-cell static">
                                <label id="label_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                       wire:key="label_{{ $subQuestion->getKey() }}_{{ $questionAnswer->getKey() }}"
                                       class="block items-center justify-center"
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
