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
        <div class="flex items-center space-x-4 mb-4 flex-wrap">
            {{ $files }}

            <x-input.filepond wire:model="{{ $uploadModel }}"
                              class="flex items-center space-x-4 flex-wrap"
                              multiple="{{ $multiple ? 'true' : 'false' }}"
                              :showDefault="$defaultFilepond"
            >
                {{ $filepond }}
            </x-input.filepond>
        </div>
    </div>


    {{ $slot }}

</x-content-section>


    <script>
        function handleFileDrop(ev) {


            var filepond = document.getElementById('filepond-upload');

            for (var i = 0; i < ev.dataTransfer.items.length; i++) {

                filepond._x_dataStack[0].$data.post.addFile(ev.dataTransfer.items[i].getAsFile());
            }
        }
    </script>
