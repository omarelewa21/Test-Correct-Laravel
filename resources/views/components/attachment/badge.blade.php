@php
$type = $attachment->getFileType();
@endphp
<div class="flex border rounded-lg border-blue-grey items-center">
    <div class="flex p-2 border-r border-blue-grey">
        @if($type == 'image')
            <x-icon.image/>
        @elseif($type == 'video')

        @endif
    </div>
    <div class="flex base items-center">
        <span class="p-2">{{ $attachment->title }}</span>
        <span class="py-2 pl-3 pr-4 flex items-center">
            <x-icon.options/>
        </span>
    </div>
</div>