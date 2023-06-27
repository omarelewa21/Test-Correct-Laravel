@props([
    'emoji' => null,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
    'disabled' => false,
    'newComment',
])
<label class="emoji-picker-radio emoji-picker-radio-container"
       @unless($disabled || $newComment)
           @click="$dispatch('comment-emoji-updated', {
           uuid: '{{$uuid}}',
           threadId: '{{$threadId}}',
           emoji: '{{$emoji->value}}',
           iconName: '{{$emoji->getIconComponentName()}}',
           })"
       @endif
>
    <input type="radio"
           name="emoji-picker-{{$uuid}}"
           @checked(!$disabled && $checked)
           data-emoji="{{$emoji->value}}"
           data-iconName="{{$emoji->getIconComponentName()}}"
           @disabled($disabled)
    >
    <span class="emoji-picker-circle emoji-picker-rectangle"
    >
        <x-dynamic-component :component="$emoji->getIconComponentName()"></x-dynamic-component>
    </span>
</label>