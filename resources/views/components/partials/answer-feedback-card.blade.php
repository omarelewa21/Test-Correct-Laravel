@props([
    'comment',
    'viewOnly' => false,
])
@php
    $iconName = \tcCore\Http\Enums\CommentEmoji::tryFrom($comment->comment_emoji)?->getIconComponentName() ?? '';
@endphp

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
     wire:key="comment-{{$comment->uuid}}"
     x-init="
         $el.addEventListener('click', (e) => {
             setActiveComment('{{$comment->thread_id}}',  '{{$comment->uuid}}');

             if(textOverflow === true && editingComment !== '{{$comment->uuid}}') {expanded = ! expanded;};
         });
         $el.addEventListener('mouseenter', (e) => {
             setHoveringComment('{{$comment->thread_id}}',  '{{$comment->uuid}}');
         });
         $el.addEventListener('mouseleave', (e) => {
            clearHoveringComment();
         });
     "
     x-data="{
             expanded: false,
             textOverflow: false,
             setTextOverflow(timeout = 0) {
                 lineHeight = 24;
                 height = $el.querySelector('.feedback-card-message-text').scrollHeight;
                 this.textOverflow = (height > ( 3 * lineHeight ));
             }
         }"
>
    <div class="flex justify-between px-4 pt-2 w-full">
        <div class="flex space-x-2">
            <x-icon.profile-circle class="text-base flex-shrink-0"/>
            <div class="flex flex-col">
                <span class="leading-none bold feedback-card-name truncate w-[125px]">{{ $comment->user->nameFull }} dit si eenlangenaam</span>
                <span class="text-[12px] feedback-card-datetime">{{ $comment->updated_at->format('j M. \'y') }}</span>
            </div>
        </div>
        <div @class(["flex items-center justify-center", "-mr-[14px]" => !$viewOnly])>
            <span @class([
            "answer-feedback-card-icon | flex items-center justify-center h-[34px]",
            "w-9" => !$viewOnly,
            "w-6" => $viewOnly,
            ])
            data-uuid="{{$comment->uuid}}"
            >

                @if($comment->comment_emoji)
                    <x-dynamic-component
                            :component="$iconName">
                    </x-dynamic-component>
                @endif

            </span>
            @unless($viewOnly)
                <x-button.options id="comment-options-button-{{$comment->uuid}}"
                                                     context="answer-feedback"
                                                     :uuid="$comment->uuid"
                                                     size="sm"
                                                     context-data-json="{!! json_encode(['threadId' => $comment->thread_id, 'preventLivewireCall' => true]) !!}"
                >
                </x-button.options>
            @endif
        </div>
    </div>

    <div x-init="
            setTextOverflow();
         "
         class="feedback-card-message"
         :class="{ 'expanded-card': expanded, 'text-overflow-card': textOverflow }"
         x-show="editingComment !== '{{$comment->uuid}}'"
    >
        <div class="feedback-card-message-text"
             :class="{
                'line-clamp-3 max-h-[72px]': ! expanded,
             }"
        >
            {!!  $comment->message !!}
        </div>
        <div class="line-clamp-chevron"
             x-show="textOverflow"
        >
            <x-icon.chevron/>
        </div>
    </div>
    <template x-if="editingComment === '{{$comment->uuid}}'">
        <div class="flex flex-col mx-4"
             {{--x-show="editingComment === '{{$comment->uuid}}'"--}}
        >

            <x-input.comment-color-picker
                    :comment-thread-id="$comment->thread_id"
                    :value="$comment->comment_color"
                    :uuid="$comment->uuid"
            ></x-input.comment-color-picker>

            <x-input.comment-emoji-picker
                    :comment-thread-id="$comment->thread_id"
                    :uuid="$comment->uuid"
                    :value="$comment->comment_emoji"
            ></x-input.comment-emoji-picker>

            <div class="comment-feedback-editor">
                <span class="comment-feedback-editor-label">@lang('assessment.Feedback schrijven')</span>
                <x-input.rich-textarea type="update-answer-feedback"
                                       :editorId="'update-' . $comment->uuid"
                >
                    {{ $comment->message }}
                </x-input.rich-textarea>
            </div>

            <div class="flex justify-end space-x-4 h-fit mt-2 mb-4">
                <x-button.text-button size="sm"
                                      @click.stop="cancelEditingComment('{{$comment->thread_id}}','{{$comment->uuid}}', '{{$iconName}}', '{{$comment->comment_color}}')"
                >
                    <span>@lang('modal.annuleren')</span>
                </x-button.text-button>
                <x-button.cta class="block"
                              @click.stop="await updateCommentThread($el); $nextTick(()=>setTextOverflow())">
                    <span>@lang('general.save')</span>
                </x-button.cta>
            </div>


        </div>
    </template>

    <div class="answer-feedback-card-line"></div>
</div>