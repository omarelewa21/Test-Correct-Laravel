{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props(['buttonCell' => false, 'withTooltip' => false, 'slim' => false])
<td {{ $attributes->except('class') }}
    @if($withTooltip) title="{{ $slot }}" @endif
    @class([
        $attributes->get('class'),
        'px-3 whitespace-nowrap max-w-0',
        'overflow-ellipsis overflow-hidden' => !$buttonCell,
        'py-0.5' => $buttonCell && $slim,
        'py-2.5' => !$buttonCell && $slim,
        'py-5' => !$buttonCell && !$slim,
    ])
>
    {{ $slot }}
</td>
