{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props(['buttonCell' => null])
@php
    $buttonCell = $buttonCell ? 'py-2' : 'py-5 overflow-ellipsis overflow-hidden';
@endphp
<td {{ $attributes->merge(['class' => 'px-4 whitespace-nowrap max-w-0 '.$buttonCell ]) }}>
    {{ $slot }}
</td>
