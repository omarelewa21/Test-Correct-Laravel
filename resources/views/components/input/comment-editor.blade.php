<div class="ckeditor-error"
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
        }

        .ck.ck-editor__main .ck-comment-marker.ck-comment-marker--active{
            color: var(--teacher-primary);
            border: 1px solid var(--ck-color-comment-marker);
        }
    </style>
    <style id="commentMarkerStyles">
        {!!  $commentMarkerStyles !!}
    </style>
    <div wire:ignore @class(['ckeditor-disabled' => $disabled])>
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