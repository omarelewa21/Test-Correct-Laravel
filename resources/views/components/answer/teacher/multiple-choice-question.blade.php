<div class="flex multiple-choice relative">
    @if($question->isSubType('TrueFalse'))
        <div @class(["flex gap-4 items-center", "justify-between w-full" => isset($inCoLearning) && $inCoLearning])>
            <div class="flex gap-4 items-center">
                <div class="bold">
                    {!! $answerStruct->first(fn($link) => $link->active)?->answer ?? '......' !!}
                </div>
                <div>
                    {!! $question->converted_question_html  !!}
                </div>
            </div>
            @if($studentAnswer)
                <div>
                    <x-button.true-false-toggle :wireKey="'toggle-'.$answer->uuid"
                                                :initialStatus="$trueFalseToggleActive"
                                                :toggleValue="$question->score"
                                                :disabled="$disabledToggle || $answerStruct->where('active', true)->isEmpty()"
                                                :identifier="$question->id"
                    />
                </div>
            @endisset
        </div>
    @endif

    @if($question->isSubType('MultipleChoice'))
        <div @class([
                  'grid gap-2 relative',
                  'grid-cols-2 w-full' => $studentAnswer && !$question->all_or_nothing,
                  'grid-cols-1 w-1/2' => !$studentAnswer || $question->all_or_nothing,
                ])>
            @foreach($answerStruct as $answerLink)
                @php
                    $activeAnswers = $answerStruct->where('active', true);
                    $firstActiveAnswer = $activeAnswers->first() === $answerLink;
                    $allButFirstActiveAnswerIds = $activeAnswers->where('multiple_choice_question_answer_id', '!=',$answerLink->multiple_choice_question_answer_id)->pluck('multiple_choice_question_answer_id');
                @endphp
                <div @class([
                    'flex items-center flex-col flex-1 relative',
                    'first-active' => $firstActiveAnswer,
                 ])
                     @if($answerLink->active && $question->all_or_nothing && $firstActiveAnswer)
                         x-data="multipleChoiceAllOrNothingLines(@js($allButFirstActiveAnswerIds), @js($studentAnswer))"
                     x-cloak
                     @endif
                     data-active-item="@js($answerLink->multiple_choice_question_answer_id)"
                >
                    <label for="link{{ $answerLink->multiple_choice_question_answer_id }}"
                            @class([
                                'relative w-full flex px-6 py-4 border-2 border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none justify-between pointer-events-none',
                                'disabled' => !$answerLink->active,
                                'active' => $answerLink->active,
                            ])
                    >
                        <input id="link{{ $answerLink->multiple_choice_question_answer_id }}"
                               name="Question_{{ $question->id }}"
                               type="radio"
                               class="hidden"
                               value="{{ $answerLink->multiple_choice_question_answer_id }}"
                        >
                        <span class="">{!! $answerLink->answer !!}</span>
                        <div @class(['hidden' => !$answerLink->active || $studentAnswer])>
                            <x-icon.checkmark />
                        </div>
                    </label>
                    @if($answerLink->active && $question->all_or_nothing && $firstActiveAnswer)
                        @foreach($allButFirstActiveAnswerIds as $activeAnswer)
                            <div wire:ignore
                                 class="all-or-nothing-line"
                                 data-line="@js($activeAnswer)"
                            ></div>
                        @endforeach
                    @endif
                </div>
                @if($studentAnswer && !$question->all_or_nothing)
                    <div class="flex items-center">
                        @if($answerLink->active)
                            <x-button.true-false-toggle :wireKey="'toggle-'.$answer->uuid.$loop->iteration"
                                                        :initialStatus="$answerLink->toggleStatus"
                                                        :toggleValue="$answerLink->score"
                                                        :identifier="$answerLink->order"
                                                        :disabled="$disabledToggle"
                            />
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
        @if($studentAnswer && $question->all_or_nothing && $answer->isAnswered)
            <div class="all-or-nothing-toggle" wire:ignore>
                <x-button.true-false-toggle :wireKey="'toggle-'.$answer->uuid"
                                            :initialStatus="$allOrNothingToggleActive"
                                            :toggleValue="$question->score"
                                            :identifier="$question->id"
                                            :disabled="$disabledToggle"
                />
            </div>
        @endif
    @endif

    @if($question->isSubType('ARQ'))
        <div class="flex w-full flex-col">
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