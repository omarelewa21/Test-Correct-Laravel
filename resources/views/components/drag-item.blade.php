@props([
'sortId' => false,
'wireKey' => false,
'useHandle' => false,
'after' => false,
'keepWidth' => false,
'sortIcon' => 'grab',
'alignItems' => 'center',
])
@php
    $alignClass = 'items-'.$alignItems;
@endphp

<div id="{{ $attributes->get('id') }}"
     {{ $attributes->merge(['class' => 'bg-system-secondary base border-system-secondary border-2
     rounded-10 inline-flex px-4 py-1.5 justify-between drag-item bold font-size-18 '. $alignClass]) }}
     @if($sortId)
         wire:sortable.item="{{ $sortId }}"
     @endif
     @if($wireKey)
         wire:key="{{ $wireKey }}"
     @endif
    {{ $attributes }}
    @if($keepWidth)
     x-data=""
     {{-- RR: Disabled the tabchange-width-mess because we switched to a different draggable library which should not have the issue of losing width when dragging; --}}
{{--     :style="'width:' +$el.parentElement?.offsetWidth+'px'"--}}
{{--     @tabchange.window="console.log('kaas');$nextTick(() => {$el.style.width = $el.parentElement?.offsetWidth+'px'})"--}}
{{--     @resize.window="$el.style.width = 'auto'"--}}
{{--     @resize.window.debounce.75ms="$el.style.width = $el.parentElement?.offsetWidth+'px'"--}}
     @endif
>
    <span id="span_{{ $attributes->get('id') }}"
          class="mr-3 flex {{ $attributes->get('slotClasses') }} {{ $alignClass }}">{!! $slot !!}</span>
    @if($after)
        <div class="flex space-x-2.5 pdf-100 items-center {{ $alignItems == 'start' ? 'mt-1.5' : '' }}">
            @endif
            <div id="icon_{{ $attributes->get('id') }}" class="w-4 pdf-80 {{ $attributes->get('dragClasses') }}">
                @if($sortIcon == 'reorder')
                    @if($useHandle)
                        <x-icon.reorder wire:sortable.handle id="grab_{{ $attributes->get('id') }}"
                                class="{{ $attributes->get('dragIconClasses') }}"
                        ></x-icon.reorder>
                    @else
                        <x-icon.reorder id="grab_{{ $attributes->get('id') }}"
                                        class="{{ $attributes->get('dragIconClasses') }}">
                        </x-icon.reorder>
                    @endif
                @else
                    @if($useHandle)
                        <x-icon.grab wire:sortable.handle id="grab_{{ $attributes->get('id') }}"
                                     class="{{ $attributes->get('dragIconClasses') }}"></x-icon.grab>
                    @else
                        <x-icon.grab id="grab_{{ $attributes->get('id') }}"
                                     class="{{ $attributes->get('dragIconClasses') }}"></x-icon.grab>
                    @endif
                @endif
            </div>

            @if($after)
                {{ $after }}
        </div>
    @endif
</div>
