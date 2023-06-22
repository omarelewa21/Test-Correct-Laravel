@props([
    'emoji' => null,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
    'disabled' => false,
])
<label class="emoji-picker-radio emoji-picker-radio-container"
>
    <input type="radio"
           name="emoji-picker-{{$uuid}}"
           @checked(!$disabled && $checked)
           data-emoji="{{$emoji->value}}"
           @disabled($disabled)
    >
    <span class="emoji-picker-circle"
          @unless($disabled)
            @click="$dispatch('comment-emoji-updated', { threadId: '{{$threadId}}', emoji: '{{$emoji->value}}' })"
          @endif
    >
        <x-dynamic-component :component="$emoji->getIconComponentName()"></x-dynamic-component>
    </span>
</label>