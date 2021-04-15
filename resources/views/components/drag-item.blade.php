@props([
'sortId' => false,
'wireKey' => false,
])


<div id="drag-item{{$sortId}}"
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
    <span id="drag-item-span{{$sortId}}" class="mr-3 flex items-center" >{{ $slot }}</span>
    <div id="drag-item-icon{{$sortId}}" class="w-4">
        <x-icon.grab class="cursor-pointer"></x-icon.grab>
    </div>

</div>
