<button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'button space-x-2.5',
        ]) }}>
    {{ $slot }}
</button>