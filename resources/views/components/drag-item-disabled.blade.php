@props([
'sortId' => false,
'wireKey' => false,
])


<div id="drag-item"
     class="bg-light-grey base border-light-grey border-2
     rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18"
     @if($sortId)
        wire:sortable.item="{{ $sortId }}"
     @endif
     @if($wireKey)
         wire:key="{{ $wireKey }}"
     @endif
    {{ $attributes }}
>
    <span class="mr-3 flex items-center">{{ $slot }}</span>
    <div class="w-4">
        <x-icon.grab/>
    </div>
</div>
