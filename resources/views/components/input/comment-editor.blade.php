<div class="ckeditor-error relative"
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor">
    <style>
        :root {
            --active-comment-color: #ff00aa;
            --ck-color-comment-marker-active: var(--active-comment-color);
        }

        .ck.ck-editor__main .ck-comment-marker {
            border: 1px solid transparent;
            display: inline-block;
            margin-top: 12px;
        }

        .ck.ck-editor__main .ck-comment-marker.ck-comment-marker--active{
            color: var(--teacher-primary);
            border: 1px solid var(--ck-color-comment-marker);
        }

    </style>
    <style id="commentMarkerStyles" wire:key="ts-{{now()->timestamp}}">
        {!!  $commentMarkerStyles !!}
    </style>
    <template x-for="thread in @js($commentThreads)">
        <div class="absolute z-10 cursor-pointer" x-init="

            setTimeout(() => {
                commentMarkers = document.querySelectorAll(`[data-comment='` + thread.threadId+ `']`);
                lastCommentMarker = commentMarkers[commentMarkers.length-1];

                $el.style.top = (lastCommentMarker.offsetTop - 15) + 'px';
                $el.style.left = (lastCommentMarker.offsetWidth + lastCommentMarker.offsetLeft - 5) + 'px';
            }, 200)

            $el.addEventListener('click', () => {
                activeThread = thread.threadId;
            });
        ">
            <x-icon.feedback-text/>
        </div>
    </template>
    <div wire:ignore @class(['ckeditor-disabled' => $disabled, 'relative'])>
        <textarea
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-data=""
                x-init="{{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @disabled($disabled)
                x-on:reinitialize-editor-{{ $editorId }}.window="{{ $initFunctionCall }}"
        > {{ $slot ?? '' }} </textarea>
    </div>
</div>