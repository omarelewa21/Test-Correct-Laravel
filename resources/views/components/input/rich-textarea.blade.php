<div class="ckeditor-error "
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor"
    x-on:drop.stop="checkMimeType(event)"
    x-data="ckEditorInlineImageUpload( '{{ __('cms.ckeditor_file_type_not_allowed') . __('cms.bestand') . ': ' }}' )"
>
    <div wire:ignore @class(['ckeditor-disabled' => $disabled])>
        <textarea
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-data=""
                x-init="await {{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @disabled($disabled)
                x-on:reinitialize-editor-{{ $editorId }}.window="await {{ $initFunctionCall }}"
        > {{ $slot ?? '' }} </textarea>
    </div>
</div>