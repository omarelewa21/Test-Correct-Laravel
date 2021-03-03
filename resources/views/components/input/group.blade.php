{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

@props([
    'label' => false,
    'for' => false,
    'error' => false,
    'helpText' => false,
])

<div {{ $attributes->merge(['class' => 'input-group']) }}>
    {{ $slot }}
    @if($label)
        <label for="{{ $for }}" class="transition ease-in-out duration-150">{{ $label }}</label>
    @endif
    @if ($error)
        <div class="mt-1 text-red-500 text-sm">{{ $error }}</div>
    @endif

    @if ($helpText)
        <p class="mt-2 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
</div>