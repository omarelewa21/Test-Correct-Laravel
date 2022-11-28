@props([
'title',
'files',
'filepond',
'uploadModel',
'multiple' => false,
'defaultFilepond' => true,
'enableUpload' => false
])

<x-content-section x-data="{attachmentOverlay: false}" :withUploadHandling="$enableUpload">
    <x-slot name="title">
        {{ $title }}
    </x-slot>
    <div class="flex mb-4 flex-wrap relative">
        {{ $files }}
        <div>
            <x-input.filepond wire:model="{{ $uploadModel }}"
                              class="flex space-x-4 flex-wrap"
                              multiple="{{ $multiple ? 'true' : 'false' }}"
                              :showDefault="$defaultFilepond"
            >
                {{ $filepond }}
            </x-input.filepond>
            @error($uploadModel)
            <span class="text-base all-red">{{ __('cms.file type not allowed') }}</span>
            @enderror
        </div>
        <div class="flex flex-1 p-4 absolute -inset-2.5 rounded-10 bg-secondary/30 items-center justify-center"
             @filepond-start.window="attachmentOverlay = true;"
             @filepond-finished.window="attachmentOverlay = false;"
             x-show="attachmentOverlay"
             x-transition
             x-cloak
        >
            <div class="flex  bg-white/95 rounded-md px-4 py-1">
                <span class="">{{ __('cms.one_moment_please') }} {{ __('cms.attachment_uploading_message') }}</span>

            </div>
        </div>
    </div>


    {{ $slot }}
</x-content-section>
