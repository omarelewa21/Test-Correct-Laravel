@props(['maxColumns' => 3])
@php
    $cols = collect([ 1 => 'grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'xl:grid-cols-3',]);
    $columnClasses = $cols->where(fn($class, $key) => $key <= $maxColumns)->join(' ');
@endphp
<div @class(["grid gap-6", $attributes->get('class'), $columnClasses]) {{ $attributes->except('class') }}>
    {{ $slot }}
</div>