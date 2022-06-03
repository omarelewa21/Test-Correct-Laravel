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
    'placeholder' => null,
    'trailingAddOn' => null
])

@php
  $errorClass = '';

  if($this->errorBag->has($attributes->wire('model')->value)) {
    $errorClass = '!border !border-allred';
    };
@endphp

  <select class="form-input {{ $errorClass }}{{ $attributes['class'] }}" {{ $attributes->except('class') }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    {{ $slot }}
  </select>
