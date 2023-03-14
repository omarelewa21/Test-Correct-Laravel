<div class="flex items-start flex-wrap co-learning-completion">
    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
        @foreach($textPartialArray as $textPartial)
            {!!$textPartial!!}
        @endforeach
        <div class="flex flex-col mx-2 mb-1">
            <span class="bold w-full flex justify-center mb-1 px-4">
                @if(isset($answerStruct[$answerIndex]) && $answerStruct[$answerIndex]['given'])
                    {!! $answerStruct[$answerIndex]['given'] !== '' ?  $answerStruct[$answerIndex]['given'] : '......' !!}
                @else
                    ......
                @endif
            </span>
            @if($studentAnswer)
                <x-button.true-false-toggle :disabled="$answerStruct[$answerIndex]['given'] === ''"
                                            :initial-value="$answerStruct[$answerIndex]['given'] !== '' ? $answerStruct[$answerIndex]['correct'] : null"
                />
            @endif
        </div>
    @endforeach
    @foreach($questionTextPartialFinal as $textPartial)
        {!!$textPartial!!}
    @endforeach
</div>
