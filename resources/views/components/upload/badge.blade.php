@php
    $type = 'upload'//$attachment->getFileType();
@endphp
<button onclick="window.open('{{ $tempUrl }}', '_blank')"
        class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2.5"
        x-data="{options: false}"
>
    <div class="flex p-2 border-r border-blue-grey h-full items-center">
        <x-icon.attachment/>
    </div>
    <div class="flex base items-center relative">
        <span class="p-2 text-base">{{ $name }}</span>
        <span class="py-3 pl-3 pr-4 flex items-center h-full" @click="options = true" @click.outside="options=false">
            <x-icon.options/>
        </span>

        <template x-if="options">
            <div class="hidden absolute right-0 top-10 bg-white">
                <span>hallo</span>
            </div>
        </template>
    </div>
</button>