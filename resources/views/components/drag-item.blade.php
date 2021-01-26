@props([
    'sortId' => '',
    'wireKey' => ''
])


<div id="drag-item"
     class="bg-system-secondary base border-system-secondary drag-item inline-flex mb-3 mr-2 rounded-10 border-2 bold font-size-18 flex justify-between items-center px-4 p-1 select-none cursor-pointer"
     wire:sortable.item="{{ $sortId }}"
     wire:key="{{ $wireKey }}"
>
    <span class="mr-3">{{ $slot }}</span>

    <x-icon.grab wire:sortable.handle/>
</div>
