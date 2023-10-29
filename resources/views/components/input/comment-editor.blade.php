<div class="ckeditor-error relative comment-editor isolate"
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor">
    <style >
        .ck.ck-balloon-panel {
            display: none !important;
        }
        .ck .ck-widget,
        .ck .ck-widget:hover {
            outline: none !important
        }
        .ck .ck-widget.ck-widget_selected, .ck .ck-widget.ck-widget_selected:hover {
            outline: none !important
        }
        .ck-math-widget.ck-comment-marker img.Wirisformula {
            background-color: var(--ck-color-comment-marker)
        }

    </style>
    <style id="addFeedbackMarkerStyles">
        :root {
            --active-comment-color: rgba(71,129,255, 0.4); /* default color, overwrite when color picker is used */
            --ck-color-comment-marker-active: var(--active-comment-color);
        }
        span.ck-comment-marker[data-comment="new-comment-thread"]{
            --active-comment-color: rgba(71,129,255, 0.4); /* default color, overwrite when color picker is used */
            --ck-color-comment-marker: var(--active-comment-color);
            --ck-color-comment-marker-active: var(--active-comment-color);
        }
    </style>
    <style id="hoveringCommentMarkerStyle" wire:ignore>{{-- filled with javascript --}}</style>
    <style id="activeCommentMarkerStyle" wire:ignore>{{-- filled with javascript --}}</style>
    <style id="temporaryCommentMarkerStyles">{{-- filled with javascript --}}</style>
    <style id="commentMarkerStyles">
        {!!  $commentMarkerStyles !!}
    </style>

    <div class="answer-feedback-comment-icons"
         x-init="await $nextTick(); initCommentIcons(@js($commentThreads), @js($answerFeedbackFilter))"
         x-on:drawer-collapse.window="setTimeout(()=>repositionAnswerFeedbackIcons(), 500)" {{-- timeout same time as transition duration of the drawer --}}
    >
        <x-partials.comment-emoji-templates/>
    </div>

    <div wire:ignore  @class(['ckeditor-disabled' => $disabled, 'relative'])>
        <textarea
                wire:key="{{ sprintf('comment-editor-%s-%s', $answerFeedbackFilter?->value, $answerUpdatedAtHash) }} "
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-init="{{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @disabled($disabled)
                x-on:reinitialize-editor-{{ $editorId }}.window="{{ $initFunctionCall }}"
        > {{ $slot ?? '' }} </textarea>
    </div>
</div>