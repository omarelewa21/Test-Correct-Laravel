<button {{ $attributes->merge(['class' => 'flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full']) }}>
    <span class="w-5 flex justify-center">{{ $icon }}</span>
    <span class="bold">{{ $text }}</span>
</button>