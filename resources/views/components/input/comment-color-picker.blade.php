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
                    <x-button.colored-circle
                            :color="$case->getRgbColorCode()"
                            @click="$dispatch('comment-color-updated', { threadId: '{{$commentThreadId}}', color: '{{$case->value}}' })"
                    ></x-button.colored-circle>
                @else
                    <x-button.colored-circle
                            :color="$case->getRgbColorCode()"
                            disabled
                    ></x-button.colored-circle>
                @endif
            @endforeach
        </div>

    </div>
</div>