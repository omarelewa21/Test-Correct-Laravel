@props([
'sortId' => false,
'wireKey' => false,
])


<div id="drag-item"
     class="max-w-max bg-system-secondary base border-system-secondary drag-item inline-flex mb-3 mr-2 rounded-10 border-2 bold font-size-18 flex justify-between items-center px-4 p-1 select-none cursor-pointer"
     @if($sortId)
     wire:sortable.item="{{ $sortId }}"
     @endif
     @if($wireKey)
     wire:key="{{ $wireKey }}"
    @endif
    {{ $attributes }}
>
    <span class="mr-3">{{ $slot }}</span>

    <x-icon.grab
        wire:sortable.handle
    />
</div>
