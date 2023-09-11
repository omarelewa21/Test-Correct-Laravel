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
        x-data="{
            uuid: '{{$uuid}}',
            threadId: '{{$threadId}}',
            emojiName: '{{$emoji->value}}',
            iconName: '{{$emoji->getIconComponentName()}}',
            toggleEmoji(event) {
                let updateEmojiData = {
                    uuid: this.uuid,
                    threadId: this.threadId,
                };

                if(this.emojiName === this.checkedEmoji) {
                    updateEmojiData.emoji = '';
                    updateEmojiData.iconName = '';
                    $el.querySelector('input').checked = false;
                    this.checkedEmoji = null;

                    return this.$dispatch('comment-emoji-updated', updateEmojiData);
                }

                updateEmojiData.emoji = this.emojiName;
                updateEmojiData.iconName = this.iconName;
                this.checkedEmoji = updateEmojiData.emoji;

                return this.$dispatch('comment-emoji-updated', updateEmojiData);
            }
        }"
>
    @unless($useCkEditorView)
    <label class="emoji-picker-radio emoji-picker-radio-container"
           @unless($disabled || $newComment)
               x-on:click="toggleEmoji($event)"
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