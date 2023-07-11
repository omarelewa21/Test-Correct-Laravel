@props([
    'emoji' => null,
    'uuid' => '',
    'threadId' => '',
    'checked' => false,
    'disabled' => false,
    'newComment',
    'useCkEditorView' => false,
])
<div @if($useCkEditorView)
         {{-- CkEditor views are rendered by javascript and are needed to keep the focustracking of the comment editor working on safari --}}
         x-init=" setTimeout(() => {
                    createCommentIconRadioButton(
                        $el,
                        @js( $emoji->getIconComponentName() ),
                        @js( $emoji->value ),
                        @js( $checked ),
                    )
                 }, 1000);
         "
        @endif
>
    @unless($useCkEditorView)
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
        <span class="emoji-picker-circle"
        >
            <x-dynamic-component :component="$emoji->getIconComponentName()"></x-dynamic-component>
        </span>
    </label>
    @else
        <template>
            <x-dynamic-component :component="$emoji->getIconComponentName()"></x-dynamic-component>
        </template>
    @endif
</div>