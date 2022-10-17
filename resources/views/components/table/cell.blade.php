{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props(['buttonCell' => null, 'withTooltip' => false, 'slim' => false])
@php
    if($buttonCell) {
        $buttonCell = $slim ? 'py-0.5' : 'py-2';
    } else {
        $buttonCell = $slim ? 'py-0.5 overflow-ellipsis overflow-hidden' : 'py-5 overflow-ellipsis overflow-hidden';
    }
@endphp
<td {{ $attributes->merge(['class' => 'px-3 whitespace-nowrap max-w-0 ' . $buttonCell ]) }}
    @if($withTooltip) title="{{ $slot }}" @endif
>
    {{ $slot }}
</td>
