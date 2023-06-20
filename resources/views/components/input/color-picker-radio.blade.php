@props([
    'color' => \tcCore\Http\Enums\CommentMarkerColor::BLUE,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
])
<label class="color-picker-radio color-picker-radio-container"
>
    <input type="radio"
           name="color-picker-{{$uuid}}"
           @checked($checked)
           data-color="{{$color->value}}"
    >
    <span class="color-picker-circle" style="background-color: {{$color->getRgbColorCode()}};"
          {{-- todo dispatch not working or not receiving --}}
          @click="$dispatch('comment-color-updated', { threadId: '{{$threadId}}', color: '{{$color->value}}' }); console.log('yes')"
    ></span>
</label>