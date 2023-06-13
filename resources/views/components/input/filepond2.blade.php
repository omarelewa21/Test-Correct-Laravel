@props(['multiple' => false, 'showDefault' => false])
<div {{ $attributes->merge() }}
     id="filepond-upload"
    wire:ignore
    x-data="{}"
    x-init="() => {
        const post = FilePond.create($refs.fileupload);
        post.setOptions({
            server: {
                process:(fieldName, file, metadata, load, error, progress, abort, transfer, options) => {

                    @this.upload('{{ $attributes->whereStartsWith('wire:model')->first() }}', file, load, error, progress)
                },
                revert: (filename, load) => {
                    @this.removeUpload('{{ $attributes->whereStartsWith('wire:model')->first() }}', filename, load)
                },
            },
            allowMultiple: true
        });
    }"
>
    {{ $slot }}
    <input type="file" x-ref="fileupload" class="{{ $showDefault ? '' : 'hidden' }}" multiple/>
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
