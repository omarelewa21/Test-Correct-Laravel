<div class="flex">
    @if($question->isSubType('TrueFalse'))
        <div class="flex gap-4 items-center">
            <div class="bold">
                {!! $answerStruct->first(fn($link) => $link->active)->answer !!}
            </div>
            <div>
                {!! $question->converted_question_html  !!}
            </div>
            @if($studentAnswer)
                <div>
                    <x-button.true-false-toggle :wireKey="'toggle-'.$answer->uuid"
                                                :initialValue="$answerStruct->first(fn($link) => $link->active)->score > 0"
                    />
                </div>
            @endisset
        </div>
    @endif

    @if($question->isSubType('MultipleChoice'))
        <div @class([
                  'grid gap-2',
                  'grid-cols-2 w-full' => $studentAnswer,
                  'grid-cols-1 w-1/2' => !$studentAnswer,
                ])>
            @foreach($answerStruct as $answerLink)

                <div class="flex items-center flex-col flex-1">
                    <label for="link{{ $answerLink->multiple_choice_question_answer_id }}"
                            @class([
                                'relative w-full flex px-6 py-4 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between pointer-events-none',
                                'disabled' => !$answerLink->active,
                                'active' => $answerLink->active,
                            ])
                    >
                        <input
                                id="link{{ $answerLink->multiple_choice_question_answer_id }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $answerLink->multiple_choice_question_answer_id }}"
                        >
                        <span class="truncate">{!! $answerLink->answer !!}</span>
                        <div @class(['hidden' => !$answerLink->active])>
                            <x-icon.checkmark />
                        </div>
                    </label>
                </div>
                @if($studentAnswer && $answerLink->active)
                    <div class="flex items-center">
                        <x-button.true-false-toggle :wireKey="'toggle-'.$answer->uuid.$loop->iteration"
                                                    :initialValue="$answerLink->active && $answerLink->score > 0" />
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if($question->isSubType('ARQ'))
        <div class="flex w-1/2 flex-col">
            <div>
                <div class="px-5 space-x-4 text-base bold flex flex-row">
                    <span class="w-16">{{__('test_take.option')}}</span>
                    <span class="w-20">{{__('test_take.thesis')}} 1</span>
                    <span class="w-20">{{__('test_take.thesis')}} 2</span>
                    <span class="w-10">{{__('test_take.reason')}}</span>
                </div>
            </div>
            <div class="divider my-2"></div>
            <div class="flex flex-col gap-2">
                @foreach($answerStruct as $answerLink)
                    <label @class([
                            'flex px-6 py-4 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none pointer-events-none',
                            'disabled' => !$answerLink->active,
                            'active' => $answerLink->active,
                        ])
                           for="link{{ $answerLink->multiple_choice_question_answer_id }}">
                        <input
                                id="link{{ $answerLink->multiple_choice_question_answer_id }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="hidden"
                                value="{{ $answerLink->multiple_choice_question_answer_id }}"
                        >
                        <span class="w-16 mr-4">{{ __($arqStructure[$loop->index][0]) }}</span>
                        <span class="w-20 mr-4">{{ __($arqStructure[$loop->index][1]) }}</span>
                        <span class="w-20 mr-4">{{ __($arqStructure[$loop->index][2]) }}</span>
                        <span class="max-w-max">{{ __($arqStructure[$loop->index][3]) }}</span>
                        <div @class(['ml-auto', 'hidden' => !$answerLink->active])>
                            <x-icon.checkmark />
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>