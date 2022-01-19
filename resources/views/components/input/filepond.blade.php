@props(['multiple' => false])
<div {{ $attributes->except('wire:model') }}
     id="filepond-upload"
    wire:ignore
    x-data="{
    post: null,
    allowedTypes: ['jpg', 'jpeg', 'JPG', 'PEG', 'GIF', 'gif', 'PNG', 'png', 'PDF', 'pdf', 'mpeg', 'mp3'],
    init: () => {
        this.post = FilePond.create($refs.input);
            this.post.setOptions({
                allowMultiple: {{ $multiple }},
                maxParallelUploads: 10,
                server: {
                    process:(fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        var fileType = file.type.split('/').pop();
                        if ($data.allowedTypes.includes(fileType)) {
                            @this.upload('{{ $attributes->whereStartsWith('wire:model')->first() }}', file, (uploadedFilename) => {

                            }, () => {

                            }, (event) => {

                            })
                        } else {
                            Notify.notify('{{ __('cms.file type not allowed') }} {{ __('cms.bestand')}}: '+file.name, 'error');
                        }

                    },
                    revert: (filename, load) => {
                        @this.removeUpload('{{ $attributes->whereStartsWith('wire:model')->first() }}', filename, load)
                    },
                },
                onprocessfilestart: (file) => {
                    let dummy = document.querySelector('#attachment-badges > #dummy');
                    dummy.querySelector('span').innerHTML = file.filename;
                },

            });
    },
    newFilesReceived: (event) => {
                var files = [];
                for (var i = 0; i < event.detail.dataTransfer.items.length; i++) {
                   files.push(event.detail.dataTransfer.items[i].getAsFile());
                }
                this.post.addFiles(files)
        }
    }
"

     x-on:newfile.window="newFilesReceived($event)"
>
    {{ $slot }}
    <input {{ $attributes->wire('model') }} type="file" x-ref="input" class="hidden" name="filepond"/>
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
