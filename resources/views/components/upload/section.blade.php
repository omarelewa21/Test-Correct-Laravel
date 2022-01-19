@props([
'title',
'files',
'filepond',
'uploadModel',
'multiple' => false,
'defaultFilepond' => true,
])

<x-content-section x-data=""
                   @dragover.prevent="$el.classList.add('dragover')"
                   @dragleave.prevent="$el.classList.remove('dragover')"
                   @drop.prevent="$el.classList.remove('dragover');  $dispatch('newfile', $event)"
                   droppable
>
    <x-slot name="title">
        {{ $title }}
    </x-slot>
    <div>
        <div class="flex mb-4 flex-wrap">
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
        </div>
    </div>


    {{ $slot }}
</x-content-section>
