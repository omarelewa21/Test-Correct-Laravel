@props([
    'comment'
])

<div @class([
         "answer-feedback-card context-menu-container",
         "answer-feedback-card-teacher" => $comment->user->isA('teacher'),
         "answer-feedback-card-student" => !$comment->user->isA('teacher'),
     ])
     x-bind:class="{
         'answer-feedback-card-editing': editingComment === '{{$comment->uuid}}',
         'answer-feedback-card-active': activeComment?.uuid === '{{$comment->uuid}}',
         'answer-feedback-card-hovering': hoveringComment?.uuid === '{{$comment->uuid}}',
     }"
     data-thread-id="{{$comment->thread_id}}"
     data-uuid="{{$comment->uuid}}"
     x-init="
         $el.addEventListener('click', (e) => {
             setActiveComment('{{$comment->thread_id}}',  '{{$comment->uuid}}');
         });
         $el.addEventListener('mouseenter', (e) => {
             setHoveringComment('{{$comment->thread_id}}',  '{{$comment->uuid}}');
         });
         $el.addEventListener('mouseleave', (e) => {
            clearHoveringComment();
         });
     "
>
    <div class="flex justify-between px-4 pt-2">
        <div class="flex flex-wrap space-x-2">
            <x-icon.profile-circle class="text-base"/>
            <div class="flex flex-col">
                <span class="leading-none bold feedback-card-name">{{ $comment->user->nameFull }}</span>
                <span class="text-[12px] feedback-card-datetime">{{ $comment->updated_at->format('j M. \'y') }}</span>
            </div>
        </div>
        <div class="flex items-center justify-center -mr-[14px]">
                                                <span class="flex items-center justify-center w-9 h-[34px]">

                                                    @if($comment->comment_emoji)
                                                        <x-dynamic-component
                                                                :component="\tcCore\Http\Enums\CommentEmoji::tryFrom($comment->comment_emoji)?->getIconComponentName()">
                                                        </x-dynamic-component>
                                                    @endif

                                                </span>
            <x-button.options id="comment-options-button-{{$comment->uuid}}"
                              context="answer-feedback"
                              :uuid="$comment->uuid"
                              size="sm"
            >
            </x-button.options>
        </div>
    </div>

    <div class="line-clamp-3 max-h-[70px] mb-3 w-full px-4 feedback-card-message"
         x-show="editingComment !== '{{$comment->uuid}}'">
        {!!  $comment->message !!}
    </div>
    <div class="flex flex-col mx-4"
         x-show="editingComment === '{{$comment->uuid}}'">

        <x-input.comment-color-picker
                :comment-thread-id="$comment->thread_id"></x-input.comment-color-picker>

        <x-input.comment-emoji-picker
                :comment-uuid="$comment->uuid"></x-input.comment-emoji-picker>

        <div class="comment-feedback-editor">
            <span class="comment-feedback-editor-label">@lang('assessment.Feedback schrijven')</span>
            <x-input.rich-textarea type="update-answer-feedback"
                                   :editorId="'update-' . $comment->uuid"
                                   :allow-wsc="true"
            >
                {{ $comment->message }}
            </x-input.rich-textarea>
        </div>

        <div class="flex justify-end space-x-4 h-fit mt-2 mb-4">
            <x-button.text-button size="sm" @click="editingComment = null">
                <span>@lang('modal.annuleren')</span>
            </x-button.text-button>
            <x-button.cta class="block"
                          @click="updateCommentThread('{{$comment->uuid}}', '{{$comment->thread_id}}')">
                <span>@lang('general.save')</span>
            </x-button.cta>
        </div>
    </div>

    <div class="answer-feedback-card-line"></div>
</div>