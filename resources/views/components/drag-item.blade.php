@props([
'sortId' => false,
'wireKey' => false,
])


<div id="drag-item"
     class="bg-system-secondary base border-system-secondary border-2
     rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18"
     @if($sortId)
        wire:sortable.item="{{ $sortId }}"
     @endif
     @if($wireKey)
         wire:key="{{ $wireKey }}"
     @endif
    {{ $attributes }}
>
    <span class="mr-3 h-6 flex items-center">{{ $slot }}</span>

    <x-icon.grab></x-icon.grab>
</div>
