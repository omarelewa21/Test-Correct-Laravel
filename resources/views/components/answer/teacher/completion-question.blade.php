<span class="flex items-start flex-wrap co-learning-completion">
    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
        @foreach($textPartialArray as $textPartial)
            {!!$textPartial!!}
        @endforeach
        <span class="inline-flex flex-col mx-1 mb-1">
            <span class="bold w-full flex justify-center mb-1 ">
                @if ($studentAnswer)
                    <span class="inline-flex mx-1 relative px-2.5 py-0.5 rounded-10 bg-offwhite border border-bluegrey bold items-center gap-1 w-min align-middle">
                {!! $answerStruct->get($answerIndex)->answerText !!}
                </span>
                @else
                    @foreach( $answerStruct as $option)
                        @if ($option->tag ==  $answerIndex+1 )
                            <span class="inline-flex mx-1 relative px-2.5 py-0.5 rounded-10 bg-offwhite border border-bluegrey bold items-center gap-1 w-min align-middle">
                                {!! $option->answer !!}
                             </span>
                        @endif
                    @endforeach
                @endif

            </span>
            @if($studentAnswer && $showToggles)
                <x-button.true-false-toggle
                        :disabled="($disabledToggle && $question->isSubType('multi')) || !$answerStruct->get($answerIndex)->answered"
                        :initialStatus="$answerStruct->get($answerIndex)->activeToggle"
                        :toggleValue="$answerStruct->get($answerIndex)->score"
                        :identifier="$answerStruct->get($answerIndex)->tag"
                />
            @endif
        </span>
    @endforeach
    @foreach($questionTextPartialFinal as $textPartial)
        {!!$textPartial!!}
    @endforeach
</span>
