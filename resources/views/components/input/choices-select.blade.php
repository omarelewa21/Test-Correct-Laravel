@props(['multiple' => false, 'options' => []])

<div wire:ignore x-data="choices({{ $multiple ? 1 : 0 }}, @js($options))" class="" style="min-width: 200px">
    <select x-ref="select" :multiple="multiple"></select>
</div>