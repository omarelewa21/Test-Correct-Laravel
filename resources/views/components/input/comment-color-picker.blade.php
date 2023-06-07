<div class="w-full">
    <div class="w-full">
        markeren
    </div>
    <div class="w-full flex justify-between gap-2">
        <x-icon.marker></x-icon.marker>
        @foreach(\tcCore\Http\Enums\CommentMarkerColor::cases() as $case)
            @isset($commentThreadId)
                <x-button.colored-circle
                        :color="$case->getHexColorCode()"
                        @click="$dispatch('comment-color-updated', { threadId: '{{$commentThreadId}}', color: '{{$case->value}}' })"
                ></x-button.colored-circle>
            @else
                <x-button.colored-circle
                        :color="$case->getHexColorCode()"
                        disabled
                ></x-button.colored-circle>
            @endif
        @endforeach
    </div>
</div>