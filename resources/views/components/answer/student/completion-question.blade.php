<div class="flex items-center flex-wrap co-learning-completion">
    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
        @foreach($textPartialArray as $textPartial)
            {!!$textPartial!!}
        @endforeach
        <div class="flex mx-2 relative top-1 mb-2 px-2.5 py-0.5 rounded-10 bg-offwhite border border-bluegrey bold items-center gap-1">
            <span class="">{!! $answerStruct->get($answerIndex)->answerText !!}</span>
            @if($answerStruct->get($answerIndex)->activeToggle)
                <x-icon.checkmark class="text-cta" />
            @else
                <x-icon.close class="text-allred" />
            @endif
        </div>
    @endforeach
    @foreach($questionTextPartialFinal as $textPartial)
        {!!$textPartial!!}
    @endforeach
</div>
