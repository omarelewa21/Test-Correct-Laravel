<div {{ $attributes->merge(['class' => 'flex flex-col h-full slide-container transition-opacity']) }} x-cloak wire:ignore.self wire:key="{{ $attributes->get('x-ref') }}">
    {{ $slot }}
</div>