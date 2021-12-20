@props(['multiple' => false])
<div {{ $attributes->merge() }}
     id="filepond-upload"
    wire:ignore
    x-data="{post: null}"
    x-init="() => {
        post = FilePond.create($refs.fileupload);
        post.setOptions({
            server: {
                process:(fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    @this.upload('{{ $attributes->whereStartsWith('wire:model')->first() }}', file, load, error, progress)
                },
                revert: (filename, load) => {
                    @this.removeUpload('{{ $attributes->whereStartsWith('wire:model')->first() }}', filename, load)
                },
            },
            allowMultiple: {{ $multiple }}
        });
    }"

     x-on:newfile.window=""
>
    {{ $slot }}
    <input type="file" x-ref="fileupload" class="hidden"/>
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
