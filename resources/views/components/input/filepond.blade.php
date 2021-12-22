@props(['multiple' => false])
<div {{ $attributes->merge() }}
     id="filepond-upload"
    wire:ignore
    x-data="{
    post: null,
    init: () => {
        this.post = FilePond.create($refs.{{ $attributes->get('ref') ?? 'input' }});
            this.post.setOptions({
                allowMultiple: {{ $multiple }},
                server: {
                    process:(fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        @this.upload('{{ $attributes->whereStartsWith('wire:model')->first() }}', file, load, error, progress)
                    },
                    revert: (filename, load) => {
                        @this.removeUpload('{{ $attributes->whereStartsWith('wire:model')->first() }}', filename, load)
                    },
                },

            });
    },
    newFilesReceived: (event) => {
                for (var i = 0; i < event.detail.dataTransfer.items.length; i++) {

                    this.post.addFile(event.detail.dataTransfer.items[i].getAsFile())
                }

    }
    }
"

     x-on:newfile.window="newFilesReceived($event)"
>
    {{ $slot }}
    <input type="file" x-ref="input" class="hidden"/>
</div>
@push('styling')
    @once
        <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    @endonce
@endpush

@push('scripts')
    @once
        <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    @endonce
@endpush
