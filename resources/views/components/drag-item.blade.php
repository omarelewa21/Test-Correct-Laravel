@props([
'sortId' => false,
'wireKey' => false,
])


<div id="{{ $attributes->get('id') }}"
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
    <span id="span_{{ $attributes->get('id') }}" class="mr-3 flex items-center" >{!! $slot !!}</span>
    <div id="icon_{{ $attributes->get('id') }}" class="w-4">
        <x-icon.grab id="grab_{{ $attributes->get('id') }}" class="cursor-pointer"></x-icon.grab>
    </div>
</div>
