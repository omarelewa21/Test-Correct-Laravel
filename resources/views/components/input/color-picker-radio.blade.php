@props([
    'color' => \tcCore\Http\Enums\CommentMarkerColor::BLUE,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
    'disabled' => false,
])
<label class="color-picker-radio color-picker-radio-container"
       @unless($disabled)
           @if($threadId === 'new-comment')
               @click="$dispatch('new-comment-color-updated', { color: '{{$color->getRgbColorCode(0.4)}}' })"
       @else
           @click="$dispatch('comment-color-updated', { threadId: '{{$threadId}}', color: '{{$color->value}}' })"
        @endif
        @endif
>
    <input type="radio"
           name="color-picker-{{$uuid}}"
           @checked(!$disabled && $checked)
           data-color="{{$color->value}}"
            @disabled($disabled)
    >
    <span class="color-picker-circle" style="background-color: {{$color->getRgbColorCode()}};"
    ></span>
</label>