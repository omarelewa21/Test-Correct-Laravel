@php
  $type = $attachment->getFileType();
@endphp
<div class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2">
    <div class="flex p-2 border-r border-blue-grey h-full items-center">
        @if($type == 'image')
            <x-icon.image/>
        @elseif($type == 'video')
            <x-icon.youtube/>
        @elseif($type == 'audio')
            <x-icon.audiofile/>
        @elseif($type == 'pdf')
            <x-icon.pdf/>
        @else
            <x-icon.attachment/>
        @endif
    </div>
    <div class="flex base items-center">
        <span class="p-2 text-base max-w-[200px] truncate" title="{{ $attachment->title }}">{{ $attachment->title }}</span>
        <span class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition">
            <x-icon.options/>
        </span>
    </div>
</div>