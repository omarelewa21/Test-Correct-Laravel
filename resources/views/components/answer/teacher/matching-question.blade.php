<div>
    @if($question->isSubType('classify'))
        <div class="flex flex-col gap-2 classify">
            @if($studentAnswer)
                <div class="flex">
                    @foreach($unusedAnswers as $answerOption)
                        <x-drag-item-disabled sortableHandle="false" class="h-fit break-all"
                                              style="height:40px;border:none">
                            {!! $answerOption->answer !!}
                        </x-drag-item-disabled>
                    @endforeach
                </div>
            @endif
            <div class="gap-4 flex grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 3xl:grid-cols-4">
                @foreach($answerStruct as $index => $group)
                    <div class="flex flex-1 flex-col gap-2">
                        <div class="flex justify-center">
                            <span class="text-lg bold">{{ $group->where('type', 'LEFT')->first()->answer }}</span>
                        </div>
                        <div class="flex flex-col items-center gap-2 flex-1 border border-dashed border-bluegrey border-2 rounded-10 p-2">
                            @foreach($group->where('type', 'RIGHT') as $answerOption)
                                <x-drag-item-disabled sortableHandle="false" class="w-full h-fit break-all"
                                                      style="height:40px;border:none">
                                    {!! $answerOption->answer !!}
                                </x-drag-item-disabled>
                                @if($studentAnswer)
                                    <x-button.true-false-toggle
                                            :initial-value="$group->where('type', 'LEFT')->first()->id === $answerOption->correct_answer_id" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($question->isSubType('matching'))
        <div class="flex flex-col gap-2 matching">
            @if($studentAnswer)
                <div class="flex">
                    @foreach($unusedAnswers as $answerOption)
                        <x-drag-item-disabled sortableHandle="false" class="h-fit break-all"
                                              style="height:40px;border:none">
                            {!! $answerOption->answer !!}
                        </x-drag-item-disabled>
                    @endforeach
                </div>
            @endif
            <div class="gap-2 flex flex-col">
                @foreach($answerStruct as $pair)
                    <div class="flex w-full gap-2">
                        @foreach($pair as $answerOption)
                            @if($answerOption->type === 'LEFT')
                                <div class="flex py-2 px-4 border-2 border-lightGrey rounded-10 bg-white font-size-18 bold base leading-5 w-1/3 h-fit break-all">
                                    {!! $answerOption->answer !!}
                                </div>
                            @else
                                <div class="flex flex-1 gap-4">
                                    <x-drag-item-disabled sortableHandle="false" class="flex-1 h-fit break-all"
                                                          style="height:40px;border:none">
                                        {!! $answerOption->answer !!}
                                    </x-drag-item-disabled>
                                    @if($studentAnswer)
                                        <x-button.true-false-toggle
                                                :initial-value="$pair->where('type','LEFT')->first()->id === $answerOption->correct_answer_id" />
                                    @endif
                                </div>
                            @endif
                            @if($loop->count === 1)
                                <div class="flex flex-1"></div>
                            @endif
                        @endforeach

                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>