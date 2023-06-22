@props([
    'emoji' => null,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
    'disabled' => false,
])
<label class="emoji-picker-radio emoji-picker-radio-container"
       @unless($disabled)
           @click="$dispatch('comment-emoji-updated', { uuid: '{{$uuid}}', emoji: '{{$emoji->value}}' })"
       @endif
>
    <input type="radio"
           name="emoji-picker-{{$uuid}}"
           @checked(!$disabled && $checked)
           data-emoji="{{$emoji->value}}"
           @disabled($disabled)
    >
    <span class="emoji-picker-circle emoji-picker-rectangle"
    >
        <x-dynamic-component :component="$emoji->getIconComponentName()"></x-dynamic-component>
    </span>
</label>