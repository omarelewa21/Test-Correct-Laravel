<div class="ckeditor-error relative comment-editor isolate"
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor">
    <style id="addFeedbackMarkerStyles">
        :root {
            --active-comment-color: rgba(var(--primary-rgb), 0.4); /* default color, overwrite when color picker is used */
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
         x-init="initCommentIcons(@js($commentThreads))"
    >
        <template id="checkmark-emoji">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.checkmark-emoji/>
                </span>
        </template>
        <template id="crossmark-emoji">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.crossmark-emoji/>
                </span>
        </template>
        <template id="questionmark-emoji">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.questionmark-emoji/>
                </span>
        </template>
        <template id="congratulations">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.congratulations/>
                </span>
        </template>
        <template id="thumbs-up">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.thumbs-up/>
                </span>
        </template>
        <template id="thumbs-down">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.thumbs-down/>
                </span>
        </template>
        <template id="smiley-happy-trafficlight">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.smiley-happy-trafficlight/>
                </span>
        </template>
        <template id="smiley-neutral-trafficlight">
                <span class="inline-block" style="scale: calc(2 / 3);">
                <x-icon.smiley-neutral-trafficlight/>
                </span>
        </template>
        <template id="smiley-sad-trafficlight">
                <span class="inline-block" style="scale: calc(2 / 3);" >
                <x-icon.smiley-sad-trafficlight/>
                </span>
        </template>

        <template id="default-icon">
            <x-icon.feedback/>
        </template>
    </div>

    <div wire:ignore @class(['ckeditor-disabled' => $disabled, 'relative'])>
        <textarea
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-data=""
                x-init="{{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @disabled($disabled)
                x-on:reinitialize-editor-{{ $editorId }}.window="{{ $initFunctionCall }}"
        > {!! $slot ?? '' !!} </textarea>
    </div>
</div>