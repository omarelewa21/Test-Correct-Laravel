@props([
'sortId' => false,
'wireKey' => false,
'useHandle' => false,
'after' => false,
])


<div id="{{ $attributes->get('id') }}"
     {{ $attributes->merge(['class' => 'bg-system-secondary base border-system-secondary border-2
     rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18']) }}
     @if($sortId)
        wire:sortable.item="{{ $sortId }}"
     @endif
     @if($wireKey)
         wire:key="{{ $wireKey }}"
     @endif
    {{ $attributes }}
        x-data=""
     :style="'width:' +$el.parentElement.offsetWidth+'px'"
>
    <div class="flex w-full">
        <span id="span_{{ $attributes->get('id') }}" class="mr-3 flex items-center {{ $attributes->get('slotClasses') }}" >{!! $slot !!}</span>
        <div id="icon_{{ $attributes->get('id') }}" class="w-4 {{ $attributes->get('dragClasses') }}">
            @if($useHandle)
                <x-icon.grab wire:sortable.handle id="grab_{{ $attributes->get('id') }}" class="cursor-pointer"></x-icon.grab>
            @else
                <x-icon.grab id="grab_{{ $attributes->get('id') }}" class="cursor-pointer"></x-icon.grab>
            @endif
        </div>
    </div>
    @if($after)
        {{ $after }}
    @endif
</div>
