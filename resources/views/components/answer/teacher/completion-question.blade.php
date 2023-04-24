<span class="flex items-start flex-wrap co-learning-completion">
    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
        @foreach($textPartialArray as $textPartial)
            {!!$textPartial!!}
        @endforeach
        <span class="inline-flex flex-col mx-2 mb-1">
            <span class="bold w-full flex justify-center mb-1 px-4">
                {!! $answerStruct->get($answerIndex)->answerText !!}
            </span>
            @if($studentAnswer)
                <x-button.true-false-toggle :disabled="!$answerStruct->get($answerIndex)->answered"
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
