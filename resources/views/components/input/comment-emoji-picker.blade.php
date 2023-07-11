<div class="comment-emoji-picker">
    <div class="comment-emoji-picker-label ">
        @lang('assessment.emoji invoegen')
    </div>
    <div class="w-full flex justify-between items-center h-[28px]" @if($useCkEditorView) wire:ignore @endif>
        @foreach(\tcCore\Http\Enums\CommentEmoji::cases() as $case)
            <x-input.emoji-picker-radio :emoji="$case"
                                        :threadId="$commentThreadId"
                                        :uuid="$uuid"
                                        :checked="$case->value === $value?->value"
                                        :new-comment="$newComment"
                                        :use-ck-editor-view="$useCkEditorView"
            >
            </x-input.emoji-picker-radio>

            {{--  if $value === null, checked none --}}
        @endforeach
    </div>
</div>