@props(['wordLists'])
<div @class(["word-list-container | flex flex-col flex-1 px-10 py-4", $attributes->get('class')])
     x-data="compileWordListContainer(@js($wordLists))"
     wire:ignore
     {{ $attributes->except('class') }}
>
    <template x-for="(wordList, wordListIndex) in wordLists" :key="wordList.uuid">

        {{ $item }}

    </template>

    {{ $slot }}
</div>