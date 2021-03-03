<button {{ $attributes->merge([
            'type' => 'button',
            'class' => 'button space-x-2.5 focus:outline-none',
        ]) }}>
    {{ $slot }}
</button>