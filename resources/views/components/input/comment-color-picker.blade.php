<div class="comment-color-picker">
    <div class="comment-color-picker-label  @if($disabled) disabled @endif ">
        @lang('assessment.tekst markeren')
    </div>
    <div class="w-full flex justify-between gap-5">
        <span @class(['text-midgrey' => $disabled])>
            <x-icon.marker></x-icon.marker>
        </span>
        <div class="flex w-full justify-between">
            @foreach(\tcCore\Http\Enums\CommentMarkerColor::cases() as $case)
                @unless($disabled)
                    <x-input.color-picker-radio :color="$case"
                                                :threadId="$commentThreadId"
                                                :uuid="$uuid"
                                                :checked="$case->value === $value->value"
                    />
                @else
                    <x-input.color-picker-radio :color="$case"
                                                :threadId="$commentThreadId"
                                                :uuid="$uuid"
                                                :checked="$case->value === $value->value"
                                                :disabled="true"
                    />
                @endif
            @endforeach
        </div>

    </div>
</div>