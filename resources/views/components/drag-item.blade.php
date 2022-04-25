@props([
'sortId' => false,
'wireKey' => false,
'useHandle' => false,
'after' => false,
'keepWidth' => false,
'sortIcon' => 'grab',
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
    @if($keepWidth)
     x-data=""
     :style="'width:' +$el.parentElement?.offsetWidth+'px'"
     @tabchange.window="$nextTick(() => {$el.style.width = $el.parentElement?.offsetWidth+'px'})"
     @resize.window="$el.style.width = 'auto'"
     @resize.window.debounce.75ms="$el.style.width = $el.parentElement?.offsetWidth+'px'"
     @endif
>

    <span id="span_{{ $attributes->get('id') }}" class="mr-3 flex items-center {{ $attributes->get('slotClasses') }}" >{!! $slot !!}</span>
    @if($after)
        <div class="flex items-center space-x-2.5">
    @endif
    <div id="icon_{{ $attributes->get('id') }}" class="w-4 {{ $attributes->get('dragClasses') }}">
        @if($sortIcon == 'reorder')
            @if($useHandle)
                <x-icon.reorder wire:sortable.handle id="grab_{{ $attributes->get('id') }}" class="cursor-pointer  {{ $attributes->get('dragIconClasses') }}"></x-icon.reorder>
            @else
                <x-icon.reorder id="grab_{{ $attributes->get('id') }}" class="cursor-pointer  {{ $attributes->get('dragIconClasses') }}"></x-icon.reorder>
            @endif
        @else
            @if($useHandle)
                <x-icon.grab wire:sortable.handle id="grab_{{ $attributes->get('id') }}" class="cursor-pointer {{ $attributes->get('dragIconClasses') }}"></x-icon.grab>
            @else
                <x-icon.grab id="grab_{{ $attributes->get('id') }}" class="cursor-pointer  {{ $attributes->get('dragIconClasses') }}"></x-icon.grab>
            @endif
        @endif
    </div>

    @if($after)
        {{ $after }}
        </div>
    @endif
</div>
