@props([
    'tab',
    'menu',
    'highlight' => false,
    'when' => true
])
@php
    if ($highlight) $attributes->setAttributes(['class' => $attributes->get('class') . ' group']);
@endphp
@if($when)
    <div {{ $attributes->merge(['class' => 'flex items-center relative hover:text-primary hover:bg-primary/5 px-2 cursor-pointer transition']) }}
         x-on:click="{{ $menu }} = '{{ $tab }}'"
    >
        @if($highlight)
            <span class="bold text-white bg-sysbase px-2 py-1 rounded-lg group-hover:bg-primary transition"
                  x-bind:class="{'bg-primary' : {{ $menu }} === '{{ $tab }}' }"
            >
            {{ $slot }}
        </span>
        @else
            <span class="bold" x-bind:class="{{ $menu }} === '{{ $tab }}' ? 'primary' : '' ">
            {{ $slot }}
        </span>
        @endif
        <span class="absolute w-[calc(100%-1rem)] bottom-0 left-2" style="height: 3px" x-bind:class="{{ $menu }} === '{{ $tab }}' ? 'bg-primary' : 'bg-transparent' "></span>
    </div>
@endif