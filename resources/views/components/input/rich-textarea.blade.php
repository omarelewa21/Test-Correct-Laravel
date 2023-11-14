<div class="ckeditor-error "
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor"
    x-on:drop.stop="checkMimeType(event)"
    x-data="{
    notAllowedMimeTypes: {
      tiff: 'image/tiff', svg: 'image/svg+xml', ico: 'image/vnd.microsoft.icon'
    },
    checkMimeType (event) {
        for(let i = 0;i < event.dataTransfer.files.length; i++) {

            let mimeType = event.dataTransfer.files[i].type;

            let condition = Object.values(this.notAllowedMimeTypes).includes(mimeType);

            if( condition ) {
                for(const [key, value] of Object.entries(this.notAllowedMimeTypes)) {
                    if(value === mimeType) {
                        mimeType = key;
                    }
                }

                Notify.notify('{{ __('cms.ckeditor_file_type_not_allowed') }} {{ __('cms.bestand')}}: ' + mimeType, 'error');
            }
        };

    }
}"
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