@props(['columnHeads'])
<div @class(["word-list | flex flex-col isolate border-b border-bluegrey pb-4 pt-4 first:pt-0", $attributes->get('class')])
     x-data="compileList(wordList, @js($columnHeads))"
     x-bind:data-list-uuid="wordList.uuid"
     wire:ignore
     {{ $attributes->except('class') }}
>
    <div class=" | flex flex-col ">
        {{ $slot }}
    </div>
</div>